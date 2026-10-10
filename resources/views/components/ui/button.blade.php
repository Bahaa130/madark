@props([
    'variant' => 'default',   // default | gold | outline | secondary | ghost | destructive | link
    'size' => 'default',      // default | sm | lg | icon | icon-sm
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'default' => 'bg-primary text-primary-foreground shadow-lg shadow-primary/25 hover:bg-primary/90 hover:shadow-primary/35',
        'gold' => 'bg-linear-to-l from-[#b8860b] via-[#d4af37] to-[#e8cf7a] text-[#1c1503] shadow-lg shadow-gold/25 hover:brightness-110',
        'outline' => 'border border-border bg-transparent hover:bg-secondary hover:text-secondary-foreground',
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'ghost' => 'hover:bg-secondary hover:text-secondary-foreground',
        'destructive' => 'bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90',
        'link' => 'text-primary underline-offset-4 hover:underline',
    ];

    $sizes = [
        'default' => 'h-10 px-5',
        'sm' => 'h-8 rounded-lg px-3 text-xs',
        'lg' => 'h-12 rounded-2xl px-8 text-base',
        'icon' => 'size-10',
        'icon-sm' => 'size-8 rounded-lg',
    ];

    $classes = trim(
        'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-xl text-sm font-semibold'
        . ' transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring'
        . ' focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:pointer-events-none'
        . ' disabled:opacity-50 active:scale-[0.98] [&_svg]:size-4 [&_svg]:shrink-0'
        . ' ' . ($variants[$variant] ?? $variants['default'])
        . ' ' . ($sizes[$size] ?? $sizes['default'])
        . ' ' . $attributes->get('class')
    );

    $attributes = $attributes->except(['class']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif