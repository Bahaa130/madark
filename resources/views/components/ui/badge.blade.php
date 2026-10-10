@props([
    'variant' => 'default',   // default | gold | outline | secondary | success | destructive
])

@php
    $variants = [
        'default' => 'border border-border bg-card text-foreground',
        'gold' => 'border border-gold/30 bg-gold/10 text-gold',
        'outline' => 'border border-border bg-transparent text-foreground',
        'secondary' => 'border border-transparent bg-secondary text-secondary-foreground',
        'success' => 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        'destructive' => 'border border-destructive/30 bg-destructive/10 text-destructive',
    ];

    $classes = trim(
        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold'
        . ' ' . ($variants[$variant] ?? $variants['default'])
        . ' ' . $attributes->get('class')
    );

    $attributes = $attributes->except(['class']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>