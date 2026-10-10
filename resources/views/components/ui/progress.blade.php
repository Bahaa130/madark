@props([
    'value' => 0,
    'indicatorClass' => null,
])

@php
    $clamped = min(100, max(0, (float) $value));
    $indicator = $indicatorClass ?? 'bg-linear-to-l from-primary to-primary/70';

    $class = trim('relative h-2 w-full overflow-hidden rounded-full bg-secondary '
        . $attributes->get('class'));

    $attributes = $attributes->except(['class'])->merge(['class' => $class]);
@endphp

<div role="progressbar"
     aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($clamped) }}"
     {{ $attributes }}>
    <div class="h-full rounded-full transition-all duration-700 ease-out {{ $indicator }}"
         style="width: {{ $clamped }}%"></div>
</div>