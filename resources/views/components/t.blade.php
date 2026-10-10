@props([
    'path',
    'default' => null,
])

@php
    $value = __($path, [], $default);

    // Fall back to English when an Arabic key is empty, so a missing
    // translation degrades to readable text instead of a blank label.
    if ($value === $path && app()->getLocale() === 'ar') {
        $value = trans($path, [], 'en');
    }
@endphp

{{ $value }}