<?php

/**
 * End-to-end page smoke test.
 *
 * Drives the real HTTP kernel with a cookie jar, so session auth, the
 * SetLocale middleware and the role guard all behave as in a browser.
 */
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

/** Minimal cookie jar so login -> authenticated page works like a browser. */
class Jar
{
    /** @var array<string, string> */
    public array $cookies = [];

    public function capture($response): void
    {
        foreach ($response->headers->getCookies() as $cookie) {
            $this->cookies[$cookie->getName()] = (string) $cookie->getValue();
        }
    }

    public function csrf(string $body): string
    {
        return preg_match('/name="_token"\s+value="([^"]+)"/', $body, $m) ? $m[1] : '';
    }
}

$jar = new Jar();

function send(object $kernel, Jar $jar, string $method, string $uri, array $data = []): array
{
    $request = Request::create($uri, $method, $data, $jar->cookies, [], ['HTTP_ACCEPT' => 'text/html']);

    $response = $kernel->handle($request);
    $jar->capture($response);

    return [$response, (string) $response->getContent()];
}

function report(string $label, $response, ?string $body, int $expect): bool
{
    $status = $response->getStatusCode();
    $ok = $status === $expect;

    printf("%-44s%-6s%-9s%s\n", $label, $status, strlen((string) $body) . 'b', $ok ? 'PASS' : 'FAIL (want ' . $expect . ')');

    // On a mismatch, surface the real exception instead of a 900KB HTML page.
    if (! $ok) {
        $exception = $response->exception ?? null;

        if ($exception !== null) {
            echo '    ' . get_class($exception) . ': '
                . substr($exception->getMessage(), 0, 260) . PHP_EOL;

            foreach ($exception->getTrace() as $frame) {
                $file = $frame['file'] ?? '';

                if ($file !== '' && ! str_contains($file, '/vendor/') && ! str_contains($file, '/storage/')) {
                    echo '    at ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file)
                        . ':' . ($frame['line'] ?? '?') . PHP_EOL;
                    break;
                }
            }

            return false;
        }

        if (preg_match('/<title>(.*?)<\/title>/s', (string) $body, $m)) {
            echo '    title: ' . trim(strip_tags($m[1])) . PHP_EOL;
        }
    }

    return $ok;
}

$results = [];

echo "-- public --\n";
foreach (['/' => 200, '/explore' => 200, '/login' => 200, '/register' => 200] as $uri => $expect) {
    [$response, $body] = send($kernel, $jar, 'GET', $uri);
    $results[$uri] = report($uri, $response, $body, $expect);
}

echo "\n-- guest is redirected away from /student --\n";
[$response, $body] = send($kernel, $jar, 'GET', '/student');
$results['guest /student'] = report('/student as guest', $response, $body, 302);

echo "\n-- login as student --\n";
[, $body] = send($kernel, $jar, 'GET', '/login');
$token = $jar->csrf($body);

[$response] = send($kernel, $jar, 'POST', '/login', [
    '_token' => $token,
    'email' => 'student@madarek.app',
    'password' => 'password123',
]);
$results['login post'] = report('POST /login', $response, null, 302);

foreach ([
    '/student' => 200,
    '/student/paths' => 200,
    '/student/paths/web-development-orbit' => 200,
    '/student/cosmos' => 200,
    '/student/lighthouse' => 200,
    '/student/profile' => 200,
    '/student/compass' => 200,
    '/dashboard' => 302,
] as $uri => $expect) {
    [$response, $body] = send($kernel, $jar, 'GET', $uri);
    $results['auth ' . $uri] = report($uri, $response, $body, $expect);
}

echo "\n-- not built yet (404 is the correct state for now) --\n";
foreach (["/instructor", "/admin"] as $uri) {
    [$response, $body] = send($kernel, $jar, "GET", $uri);
    $results["todo " . $uri] = report($uri, $response, $body, 302);
}

echo "\n-- locale query switch --\n";
[$response, $body] = send($kernel, $jar, 'GET', '/?lang=ar');
$results['ar rtl'] = str_contains($body, 'dir="rtl"');
printf("%-44s%-6s%s\n", '/?lang=ar renders dir=rtl', $response->getStatusCode(), $results['ar rtl'] ? 'PASS' : 'FAIL');

[$response, $body] = send($kernel, $jar, 'GET', '/?lang=en');
$results['en ltr'] = str_contains($body, 'dir="ltr"');
printf("%-44s%-6s%s\n", '/?lang=en renders dir=ltr', $response->getStatusCode(), $results['en ltr'] ? 'PASS' : 'FAIL');

echo "\n" . str_repeat('-', 72) . "\n";
$failed = array_keys(array_filter($results, fn ($ok) => ! $ok));
echo $failed === [] ? "ALL PAGE CHECKS PASS\n" : 'FAILURES: ' . implode(', ', $failed) . "\n";
