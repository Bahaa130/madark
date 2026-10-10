@php
    $base = 'p-6 pt-0';

    $class = trim($base . ' ' . $attributes->get('class'));
    $attributes = $attributes->except(['class'])->merge(['class' => $class]);
@endphp

<div {{ $attributes }}>{{ $slot }}</div>