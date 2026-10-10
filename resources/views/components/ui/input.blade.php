@props([
    'type' => 'text',
])

<input type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'flex h-11 w-full rounded-xl border border-input bg-card px-4 py-2 text-sm shadow-sm'
            . ' transition-colors placeholder:text-muted-foreground focus-visible:outline-none'
            . ' focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50'
            . ' ' . $attributes->get('class'),
    ]) }} />