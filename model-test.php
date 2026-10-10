<?php

/**
 * Model smoke test — exercises the relations the pages depend on so a wrong
 * FK or relation surfaces here instead of as a blank page.
 */
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Journey;
use App\Models\User;

$failures = [];

function check(string $label, $actual, $expected): void
{
    global $failures;

    $ok = $actual === $expected;
    printf("%-42s%-22s%s\n", $label, var_export($actual, true), $ok ? 'PASS' : 'FAIL (expected ' . var_export($expected, true) . ')');

    if (! $ok) {
        $failures[] = $label;
    }
}

echo str_repeat('-', 90), PHP_EOL;

$journey = Journey::published()->with(['modules.lessons', 'instructor'])->first();

check('a published journey exists', $journey !== null, true);

if ($journey) {
    echo "\njourney: {$journey->slug} ({$journey->title})\n";
    echo "level cast: " . get_debug_type($journey->level) . " / {$journey->level->value}\n";
    echo "status cast: " . get_debug_type($journey->status) . " / {$journey->status->value}\n\n";

    // The relation that was wrong before.
    check('lessons() count', $journey->lessons()->count(), $journey->lessonsCount());

    // Same number via the module -> lesson path.
    $viaModules = $journey->modules->sum(fn ($m) => $m->lessons->count());
    check('lessons() matches modules->lessons', $journey->lessons()->count(), $viaModules);

    // Curriculum order = modules by order, each module's lessons by order.
    $expectedOrder = $journey->modules
        ->sortBy('order')
        ->flatMap(fn ($m) => $m->lessons->sortBy('order')->pluck('order')->all())
        ->values()
        ->all();

    check('lessons() follows curriculum order', $journey->lessons()->pluck('order')->all(), $expectedOrder);

    check('instructor relation', $journey->instructor?->email !== null, true);
    check('localizedTitle(ar)', $journey->localizedTitle('ar') === $journey->title, true);

    echo "\nenrollments: " . $journey->enrollments()->count() . PHP_EOL;
}

$user = User::where('email', 'student@madarek.app')->first();
check('student user exists', $user !== null, true);

if ($user) {
    echo "\nrole cast: " . get_debug_type($user->role) . " / {$user->role->value}\n";
    check('password is hashed', str_starts_with((string) $user->password, '$2y$'), true);
    check('enrollments relation', $user->enrollments()->count() >= 1, true);
    check('progress relation', $user->lessonProgress()->count() >= 1, true);
    check('notifications relation', $user->notifications()->count() >= 1, true);
}

echo "\n" . str_repeat('-', 90) . PHP_EOL;
echo $failures === [] ? "ALL MODEL CHECKS PASS\n" : 'FAILURES: ' . implode(', ', $failures) . PHP_EOL;