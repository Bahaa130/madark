@php
    $base = 'rounded-2xl border border-border bg-card text-card-foreground shadow-sm transition-shadow duration-300';

    $class = trim($base . ' ' . $attributes->get('class'));
    $attributes = $attributes->except(['class'])->merge(['class' => $class]);
@endphp

<div {{ $attributes }}>{{ $slot }}</div>