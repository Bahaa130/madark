<?php

/**
 * Resolves every key in both dictionaries through the real Laravel translator
 * to prove nothing is a silent miss (a missing key returns the key itself).
 */
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function flatten(array $data, string $prefix = ''): array
{
    $out = [];

    foreach ($data as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
        if (is_array($value)) {
            // array_merge, not += : a list needs reindexing, otherwise `+=`
            // silently drops repeated integer keys and undercounts.
            $out = array_merge($out, flatten($value, $path));
        } else {
            $out[] = $path;
        }
    }

    return $out;
}

$failed = 0;

foreach (['ar', 'en'] as $locale) {
    $source = __DIR__ . "/lang/{$locale}.json";
    $keys = flatten(json_decode(file_get_contents($source), true, 512, JSON_THROW_ON_ERROR));

    app('translator')->setLocale($locale);

    $misses = [];
    foreach ($keys as $key) {
        $line = app('translator')->get($key);
        if ($line === $key || $line === null) {
            $misses[] = $key;
        }
    }

    $failed += count($misses);
    printf("%s: %d keys, %d unresolved%s\n", $locale, count($keys), count($misses),
        $misses ? ' -> ' . implode(', ', array_slice($misses, 0, 10)) : '');
}

echo $failed === 0 ? "ALL KEYS RESOLVE\n" : "FAILURES: {$failed}\n";