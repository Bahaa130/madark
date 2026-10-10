@php
    use App\Support\Nav;

    $pathname = request()->path();
    $pathname = $pathname === '/' ? '/' : '/' . $pathname;
    $user = auth()->user();
@endphp

<aside class="fixed inset-y-0 start-0 z-40 hidden w-72 flex-col border-e border-sidebar-border bg-sidebar lg:flex">
    <div class="flex h-16 items-center border-b border-sidebar-border px-6">
        <x-layout.logo />
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="{{ __('brand.name') }}">
        @foreach ($nav as $index => $item)
            @php $active = Nav::isActive($pathname, $item); @endphp

            <div class="animate-rise" style="animation-delay: {{ number_format($index * 0.04, 2) }}s">
                <a href="{{ $item['href'] }}"
                   @if ($active) aria-current="page" @endif
                   @class([
                       'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors duration-200',
                       'text-primary' => $active,
                       'text-muted-foreground hover:bg-sidebar-accent hover:text-foreground' => ! $active,
                   ])>
                    {{-- framer-motion layoutId pill: one absolutely positioned
                         layer that is shown only for the active item. --}}
                    <span class="absolute inset-0 rounded-xl bg-primary/10 ring-1 ring-primary/20 ring-inset"
                          @if (! $active) hidden @endif></span>

                    <x-icon :name="$item['icon']"
                            class="relative z-10 size-5 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ $active ? 'text-primary' : 'text-muted-foreground/80 group-hover:text-foreground' }}" />

                    <span class="relative z-10">{{ __($item['label']) }}</span>

                    @if ($active)
                        <span class="relative z-10 ms-auto size-1.5 rounded-full bg-gold shadow-[0_0_6px_1px_rgba(212,175,55,0.7)]"></span>
                    @endif
                </a>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-sidebar-border p-4">
        <div class="flex items-center gap-3 rounded-xl bg-sidebar-accent px-3 py-2.5">
            <span class="flex size-8 items-center justify-center rounded-full bg-linear-to-br from-primary to-indigo-900 text-[10px] font-bold text-primary-foreground">
                {{ mb_strtoupper(mb_substr((string) $user?->name, 0, 1) ?: '?') }}
            </span>

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold">{{ $user?->name }}</p>
                <p class="truncate text-xs text-muted-foreground">{{ $user?->role?->value }}</p>
            </div>
        </div>
    </div>
</aside>