<?php

$ar = json_decode(file_get_contents(__DIR__ . '/lang/ar.json'), true);
$en = json_decode(file_get_contents(__DIR__ . '/lang/en.json'), true);

function flat(array $a, string $p = ''): array
{
    $out = [];
    foreach ($a as $k => $v) {
        $key = $p === '' ? $k : "$p.$k";
        if (is_array($v)) {
            $out += flat($v, $key);
        } else {
            $out[$key] = $v;
        }
    }
    return $out;
}

$fa = flat($ar);
$fb = flat($en);

echo 'ar keys: ' . count($fa) . PHP_EOL;
echo 'en keys: ' . count($fb) . PHP_EOL;

$missingInEn = array_diff_key($fa, $fb);
$missingInAr = array_diff_key($fb, $fa);

if ($missingInEn) {
    echo 'MISSING IN EN: ' . implode(', ', array_keys($missingInEn)) . PHP_EOL;
}
if ($missingInAr) {
    echo 'MISSING IN AR: ' . implode(', ', array_keys($missingInAr)) . PHP_EOL;
}
if (! $missingInEn && ! $missingInAr) {
    echo "PARITY OK — both dictionaries have identical keys" . PHP_EOL;
}