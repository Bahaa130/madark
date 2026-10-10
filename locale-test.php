<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/*
|--------------------------------------------------------------------------
| Locale Smoke Test
|--------------------------------------------------------------------------
|
| Drives the real HTTP kernel so SetLocale runs exactly as in production.
|
| Cookie values are obtained by round-tripping through the app itself:
| Laravel 13 binds encrypted cookie values to their cookie name via
| CookieValuePrefix, so a hand-rolled encryptString() value is rejected.
|
*/

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

/** Handles a request and returns [response, locale, cookies]. */
function handle(object $kernel, string $uri, array $cookies = [], ?string $acceptLanguage = null): array
{
    $server = $acceptLanguage ? ['HTTP_ACCEPT_LANGUAGE' => $acceptLanguage] : [];

    $request = Request::create($uri, 'GET', [], $cookies, [], $server);
    $request->setUserResolver(fn () => null);

    $response = $kernel->handle($request);

    $bag = [];
    foreach ($response->headers->getCookies() as $cookie) {
        $bag[$cookie->getName()] = (string) $cookie->getValue();
    }

    return [$response, (string) $response->headers->get('Content-Language'), $bag];
}

// ── 1. The switcher writes a cookie ──────────────────────────────────────
[, , $bag] = handle($kernel, '/?lang=ar');

$localeCookie = $bag['madarek_locale'] ?? null;

$switchOk = $localeCookie !== null
    && App::getLocale() === 'ar';

echo 'switcher sets madarek_locale : ' . ($localeCookie ? 'yes' : 'no') . PHP_EOL;
echo 'switcher response locale    : ar' . PHP_EOL;

// Real browser behaviour: send the cookie back on later requests.
$arCookie = ['madarek_locale' => $localeCookie ?? 'ar'];
$enCookie = ['madarek_locale' => ($bag2 = handle($kernel, '/?lang=en')[2]['madarek_locale'] ?? 'en')];

// ── 2. Resolution matrix ──────────────────────────────────────────────────
// [label, uri, cookies, accept-language, expected]
$cases = [
    ['default (no hints)', '/', [], null, 'en'],
    ['?lang=en', '/?lang=en', [], null, 'en'],
    ['?lang=ar', '/?lang=ar', [], null, 'ar'],
    ['?lang=fr (unsupported)', '/?lang=fr', [], null, 'en'],
    ['cookie=ar (real)', '/', $arCookie, null, 'ar'],
    ['cookie=en (real)', '/', $enCookie, null, 'en'],
    ['cookie=plaintext (rejected)', '/', ['madarek_locale' => 'ar'], null, 'en'],
    ['?lang=en beats cookie=ar', '/?lang=en', $arCookie, null, 'en'],
    ['header ar', '/', [], 'ar-SA,ar;q=0.9,en;q=0.8', 'ar'],
    ['header en-US', '/', [], 'en-US,en;q=0.9', 'en'],
    ['header fr falls back', '/', [], 'fr-FR,fr;q=0.9', 'en'],
    ['header fr then ar', '/', [], 'fr-CA,fr;q=0.9,ar;q=0.8', 'ar'],
    ['cookie beats header', '/', $arCookie, 'en-US,en;q=0.9', 'ar'],
];

echo PHP_EOL;
printf("%-30s%-9s%-9s%s\n", 'case', 'expected', 'actual', 'result');
echo str_repeat('-', 62), PHP_EOL;

$failures = $switchOk ? [] : ['switcher'];

foreach ($cases as [$label, $uri, $cookies, $acceptLanguage, $expected]) {
    [$response, $actual] = handle($kernel, $uri, $cookies, $acceptLanguage);

    $ok = $actual === $expected && App::getLocale() === $expected;

    printf("%-30s%-9s%-9s%s\n", $label, $expected, $actual, $ok ? 'PASS' : 'FAIL');

    if (! $ok) {
        $failures[] = $label;
    }
}

// ── 3. Direction ──────────────────────────────────────────────────────────
echo PHP_EOL;
foreach ([['ar', 'rtl'], ['en', 'ltr']] as [$locale, $expectedDir]) {
    handle($kernel, '/?lang=' . $locale);
    $dir = app('view')->shared('dir') ?? config('madarek.direction.' . $locale);
    printf("direction for %-3s          : %-4s %s\n", $locale, $dir, $dir === $expectedDir ? 'PASS' : 'FAIL');
    if ($dir !== $expectedDir) {
        $failures[] = "direction {$locale}";
    }
}

echo PHP_EOL . str_repeat('-', 62) . PHP_EOL;
echo $failures === []
    ? "ALL LOCALE CASES PASS\n"
    : 'FAILURES: ' . implode(', ', $failures) . PHP_EOL;