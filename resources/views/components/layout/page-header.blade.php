@props([
    'title',
    'subtitle' => null,
])

<div class="animate-rise mb-8 flex flex-wrap items-end justify-between gap-4 {{ $attributes->get('class') }}">
    <div class="flex items-center gap-4">
        {{-- An icon slot, not an `icon` string: passing markup through an
             attribute makes Blade's component regex parse the inner tags. --}}
        @isset($icon)
            <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br from-primary/15 to-primary/5 text-primary ring-1 ring-primary/20 ring-inset">
                {{ $icon }}
            </span>
        @endisset

        <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $title }}</h1>

            @if ($subtitle)
                <p class="mt-1 max-w-2xl text-sm text-muted-foreground">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @isset($actions)
        {{ $actions }}
    @endisset
</div>