@props([
    'compact' => false,
    'href' => '/',
])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'group flex items-center gap-2.5 ' . $attributes->get('class')]) }}>
    <span class="relative flex size-9 items-center justify-center rounded-xl bg-linear-to-br from-primary via-indigo-700 to-indigo-950 text-white shadow-lg shadow-primary/30 transition-transform duration-300 group-hover:scale-105 group-hover:rotate-6">
        {{-- Orbit --}}
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3" />
            <ellipse cx="12" cy="12" rx="10" ry="4.5" transform="rotate(-30 12 12)" />
        </svg>
        <span class="absolute -top-0.5 -end-0.5 size-2 rounded-full bg-gold shadow-[0_0_8px_2px_rgba(212,175,55,0.6)]"></span>
    </span>

    @unless ($compact)
        <span class="flex flex-col leading-none">
            <span class="text-lg font-extrabold tracking-tight">{{ __('brand.name') }}</span>
            <span class="text-[10px] font-medium text-muted-foreground">MADAREK</span>
        </span>
    @endunless
</a>