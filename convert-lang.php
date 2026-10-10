<?php

/**
 * Generates Laravel-native translation files from the authored JSON dictionaries.
 *
 * lang/{locale}.json  ->  lang/{locale}/{group}.php
 *
 * Why one file per group instead of a single JSON file: Laravel only treats a
 * key as JSON when it contains no dots ("brand.name" is therefore a file key
 * resolving to group "brand", item "name"). Nested arrays inside each group
 * file are resolved by Arr::get, so dotted keys work as written.
 *
 * Run: php convert-lang.php
 */

$locales = ['ar', 'en'];

/** Renders a nested array as short-syntax PHP. */
function exportValue(mixed $value, int $indent): string
{
    $pad = str_repeat('    ', $indent);

    if (is_array($value)) {
        if ($value === []) {
            return '[]';
        }

        $isList = array_keys($value) === range(0, count($value) - 1);
        $lines = [];

        foreach ($value as $key => $item) {
            $renderedKey = $isList ? '' : var_export((string) $key, true) . ' => ';
            $lines[] = $pad . '    ' . $renderedKey . exportValue($item, $indent + 1) . ',';
        }

        return "[\n" . implode("\n", $lines) . "\n" . $pad . ']';
    }

    return var_export($value, true);
}

foreach ($locales as $locale) {
    $source = __DIR__ . "/lang/{$locale}.json";
    $target = __DIR__ . "/lang/{$locale}";

    if (! is_file($source)) {
        fwrite(STDERR, "missing source: {$source}\n");

        exit(1);
    }

    $data = json_decode(file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);

    if (! is_dir($target)) {
        mkdir($target, 0777, true);
    }

    // Drop stale generated files from earlier runs.
    foreach (glob($target . '/*.php') ?: [] as $stale) {
        unlink($stale);
    }

    $groups = 0;

    foreach ($data as $group => $values) {
        // Top-level scalars (e.g. "dir") are config, not a translation group.
        if (! is_array($values)) {
            continue;
        }

        $body = "<?php\n\nreturn " . exportValue($values, 0) . ";\n";
        file_put_contents($target . '/' . $group . '.php', $body);
        $groups++;
    }

    echo "lang/{$locale}: wrote {$groups} group files\n";
}

echo "done\n";