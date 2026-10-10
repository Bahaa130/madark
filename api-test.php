<?php

/**
 * End-to-end test for the enrollment and progress endpoints: completes every
 * lesson of a journey and asserts the enrollment flips to COMPLETED and a
 * certificate is issued exactly once.
 */
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Journey;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Http\Request;

/** Minimal cookie jar so session auth survives across requests. */
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
        // Prefer the meta tag the Alpine player actually sends.
        if (preg_match('/name="csrf-token"\s+content="([^"]+)"/', $body, $m)) {
            return $m[1];
        }

        return preg_match('/name="_token"\s+value="([^"]+)"/', $body, $m) ? $m[1] : '';
    }
}

$jar = new Jar();

function send(object $kernel, Jar $jar, string $method, string $uri, array $data = []): array
{
    $request = Request::create($uri, $method, $data, $jar->cookies, [], [
        'HTTP_ACCEPT' => 'application/json, text/html',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);

    $response = $kernel->handle($request);
    $jar->capture($response);

    $raw = (string) $response->getContent();

    return [$response, $raw, json_decode($raw, true)];
}

function check(string $label, bool $ok, ?string $detail = ''): void
{
    printf("%-52s%s%s\n", $label, $ok ? 'PASS' : 'FAIL', ($detail ?? '') !== '' ? '  (' . substr($detail, 0, 90) . ')' : '');

    if (! $ok) {
        $GLOBALS['failures'][] = $label;
    }
}

$failures = [];

$student = User::where('email', 'student@madarek.app')->firstOrFail();
$journey = Journey::where('slug', 'web-development-orbit')->firstOrFail();
$lessons = $journey->lessons()->orderBy('modules.order')->orderBy('lessons.order')->get();

// Start from a clean slate so the certificate path is exercised for real.
Enrollment::where('user_id', $student->id)->where('journey_id', $journey->id)->delete();
LessonProgress::where('user_id', $student->id)->delete();
$student->certificates()->delete();
$student->achievements()->delete();
$student->notifications()->delete();

echo "-- authenticate --\n";

[, $raw] = send($kernel, $jar, 'GET', '/login');
$token = $jar->csrf($raw);

send($kernel, $jar, 'POST', '/login', [
    '_token' => $token,
    'email' => 'student@madarek.app',
    'password' => 'password123',
]);

check('logged in', auth()->check());

// Logging in regenerates the session, so the pre-login CSRF token is stale.
[, $raw] = send($kernel, $jar, 'GET', '/student');
$token = $jar->csrf($raw);

check('fresh csrf token available', $token !== '');

echo "\n-- enrolling --\n";

$otherJourney = Journey::where('slug', 'ux-design-orbit')->firstOrFail();
$firstLessonOfOther = $otherJourney->lessons()->firstOrFail();

[, , $data] = send($kernel, $jar, 'POST', '/api/enroll', ['_token' => $token, 'journeyId' => $journey->id]);
check('POST /api/enroll returns 200', ($data['ok'] ?? false) === true, json_encode($data));

$enrollment = Enrollment::where('user_id', $student->id)->where('journey_id', $journey->id)->first();
check('enrollment row created', $enrollment !== null);
check('enrollment status ACTIVE', $enrollment?->status === EnrollmentStatus::ACTIVE, $enrollment?->status?->value);

echo "\n-- progress outside the enrolled journey is rejected --\n";

[, , $data] = send($kernel, $jar, 'POST', '/api/progress', [
    '_token' => $token,
    'lessonId' => $firstLessonOfOther->id,
    'completed' => true,
]);
check('unrelated lesson rejected', ($data['error'] ?? null) === 'not_enrolled', json_encode($data));

echo "\n-- completing every lesson (".$lessons->count().") --\n";

$serial = null;
$lastStatus = 0;

foreach ($lessons as $lesson) {
    [$response, , $data] = send($kernel, $jar, 'POST', '/api/progress', [
        '_token' => $token,
        'lessonId' => $lesson->id,
        'completed' => true,
    ]);

    $lastStatus = $response->getStatusCode();

    if ($lastStatus !== 200) {
        check('lesson ' . $lesson->id . ' accepted', false, json_encode($data));
        break;
    }

    $serial ??= $data['certificateSerial'] ?? null;
}

check('all lessons accepted', $lastStatus === 200);
check('LessonProgress rows = lesson count', LessonProgress::where('user_id', $student->id)->where('completed', true)->count() === $lessons->count());

$enrollment->refresh();
check('enrollment flipped to COMPLETED', $enrollment->status === EnrollmentStatus::COMPLETED, $enrollment->status->value);
check('completed_at stamped', $enrollment->completed_at !== null);
check('certificate issued', $serial !== null, (string) $serial);
check('certificate persisted once', $student->certificates()->count() === 1, (string) $student->certificates()->count());
check('achievement stored', $student->achievements()->count() === 1);
check('certificate notification stored', $student->notifications()->where('type', 'certificate')->count() === 1);

echo "\n-- replay must not duplicate the certificate --\n";

[, , $data] = send($kernel, $jar, 'POST', '/api/progress', [
    '_token' => $token,
    'lessonId' => $lessons->first()->id,
    'completed' => true,
]);

check('replay returns no new serial', ($data['certificateSerial'] ?? null) === null, json_encode($data));
check('still exactly one certificate', $student->certificates()->count() === 1);

echo "\n-- uncompleting a lesson --\n";

[, , $data] = send($kernel, $jar, 'POST', '/api/progress', [
    '_token' => $token,
    'lessonId' => $lessons->first()->id,
    'completed' => false,
]);

$row = LessonProgress::where('user_id', $student->id)->where('lesson_id', $lessons->first()->id)->first();
check('lesson marked incomplete', $row !== null && $row->completed === false);
check('completed_at cleared', $row?->completed_at === null);

echo "\n-- validation --\n";

[, , $data] = send($kernel, $jar, 'POST', '/api/progress', ['_token' => $token]);
check('missing lessonId rejected', isset($data['errors']['lessonId']), json_encode(array_keys($data['errors'] ?? [])));

echo "\n" . str_repeat('-', 72) . "\n";
echo $failures === [] ? "ALL API CHECKS PASS\n" : 'FAILURES: ' . implode(', ', $failures) . "\n";
