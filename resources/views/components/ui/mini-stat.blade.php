@props([
    'label',
    'value',
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3 rounded-xl bg-secondary/60 px-4 py-3']) }}>
    <x-slot:icon>
        <x-icon name="orbit" class="size-5 text-primary" />
    </x-slot:icon>

    {{ $icon }}

    <div class="min-w-0">
        <p class="text-lg font-extrabold leading-none">{{ $value }}</p>
        <p class="mt-1 text-[11px] text-muted-foreground">{{ $label }}</p>
    </div>
</div>
