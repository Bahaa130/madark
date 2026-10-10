@php
    $user = auth()->user();
@endphp

<header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-border bg-background/80 px-4 backdrop-blur-xl sm:px-6 lg:px-8">
    <div class="relative hidden max-w-md flex-1 sm:block">
        {{-- Search --}}
        <svg class="absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" />
        </svg>

        <x-ui.input type="search" class="h-10 rounded-full bg-card ps-9 text-sm"
                   placeholder="{{ __('common.search') }}"
                   aria-label="{{ __('common.search') }}" />
    </div>

    <div class="ms-auto flex items-center gap-1.5 sm:gap-2">
        {{-- Madarek Pulse --}}
        <button type="button" aria-label="{{ __('common.notifications') }}"
                class="relative inline-flex size-10 items-center justify-center rounded-xl transition hover:bg-secondary">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <span class="animate-pulse-soft absolute end-2.5 top-2 size-2 rounded-full bg-gold shadow-[0_0_6px_1px_rgba(212,175,55,0.8)]"></span>
        </button>

        <x-theme-toggle />
        <x-language-switcher />

        {{-- User menu (Alpine replaces the framer-motion AnimatePresence) --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open"
                    class="flex items-center rounded-full transition-transform hover:scale-105 active:scale-95"
                    aria-haspopup="menu" :aria-expanded="open"
                    aria-label="{{ $user?->name }}">
                @if ($user?->image)
                    <img src="{{ $user->image }}" alt="{{ $user->name }}" class="size-9 rounded-full object-cover">
                @else
                    <span class="flex size-9 items-center justify-center rounded-full bg-linear-to-br from-primary to-indigo-900 text-sm font-bold text-primary-foreground">
                        {{ mb_strtoupper(mb_substr((string) $user?->name, 0, 1) ?: '?') }}
                    </span>
                @endif
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1.5 scale-[0.97]"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-1.5 scale-[0.97]"
                 role="menu"
                 class="absolute end-0 top-12 w-56 overflow-hidden rounded-2xl border border-border bg-popover p-1.5 text-popover-foreground shadow-xl shadow-black/10">
                <div class="px-3 py-2">
                    <p class="truncate text-sm font-bold">{{ $user?->name }}</p>
                    <p class="truncate text-xs text-muted-foreground">{{ $user?->email }}</p>
                </div>

                <div class="my-1 h-px bg-border"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" role="menuitem"
                            class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-destructive transition-colors hover:bg-destructive/10">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <path d="M16 17l5-5-5-5M21 12H9" />
                        </svg>
                        {{ __('common.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>