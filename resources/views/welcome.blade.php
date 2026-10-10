@extends('layouts.app')

@section('title', __('nav.onboarding'))

@section('content')
<div class="min-h-dvh cosmic-bg">
    <x-layout.public-header />

    <main>
        {{-- ───────── Hero ───────── --}}
        <section class="mx-auto max-w-7xl px-4 pt-16 pb-20 sm:pt-24 sm:px-6 lg:px-8">
            <div class="animate-rise mx-auto max-w-4xl text-center">
                <x-ui.badge variant="gold" class="mb-6 gap-1.5 px-3 py-1">
                    {{-- Sparkles --}}
                    <svg class="size-3" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l1.9 5.6L19.5 9.5 13.9 11.4 12 17l-1.9-5.6L4.5 9.5l5.6-1.9L12 2z" />
                    </svg>
                    {{ __('home.badge') }}
                </x-ui.badge>

                <h1 class="text-balance text-4xl font-extrabold leading-[1.15] tracking-tight sm:text-6xl lg:text-7xl">
                    <span class="text-foreground">{{ __('home.title1') }} </span>
                    <span class="text-gold-gradient">{{ __('home.title2') }}</span>
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg">
                    {{ __('home.subtitle') }}
                </p>

                <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                    <x-ui.button size="lg" href="{{ route('register') }}" class="group">
                        {{ __('home.ctaPrimary') }}
                        @if ($dir === 'rtl')
                            <svg class="size-4 transition-transform group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                        @else
                            <svg class="size-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        @endif
                    </x-ui.button>

                    <x-ui.button size="lg" variant="outline" href="{{ route('explore') }}">
                        {{-- Telescope --}}
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6.5 6.5l11 11M4 20l4-1 11-11-3-3L5 16l-1 4zM14 4l6 6" />
                        </svg>
                        {{ __('home.ctaSecondary') }}
                    </x-ui.button>
                </div>

                <p class="mt-6 text-xs text-muted-foreground">
                    {{ $dir === 'rtl'
                        ? 'مجاني بالكامل · بلا بطاقة · ابدأ خلال دقيقة'
                        : 'Fully free · No card · Start in a minute' }}
                </p>
            </div>

            {{-- ───────── Orbital visual ───────── --}}
            <div class="animate-rise relative mx-auto mt-20 h-64 max-w-2xl sm:h-80" style="animation-delay:.2s">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="relative flex size-40 items-center justify-center sm:size-52">
                        <div class="absolute inset-0 rounded-full bg-linear-to-br from-primary/25 to-transparent blur-2xl"></div>
                        <div class="absolute inset-4 rounded-full border border-primary/30"></div>
                        <div class="absolute inset-10 rounded-full border border-gold/30"></div>

                        <div class="animate-float flex size-20 items-center justify-center rounded-full bg-linear-to-br from-primary via-indigo-700 to-indigo-950 shadow-2xl shadow-primary/30 sm:size-24">
                            {{-- Compass --}}
                            <svg class="size-9 text-white sm:size-11" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76" />
                            </svg>
                        </div>

                        {{-- Counter-rotating dashed rings (framer-motion rotate loops → CSS) --}}
                        <span class="absolute inset-4 animate-[spin_22s_linear_infinite] rounded-full border border-dashed border-primary/25">
                            <span class="absolute -top-1 start-1/2 size-2.5 rounded-full bg-gold shadow-[0_0_10px_2px_rgba(212,175,55,0.7)]"></span>
                        </span>
                        <span class="absolute inset-10 animate-[spin_34s_linear_infinite_reverse] rounded-full border border-dashed border-gold/20">
                            <span class="absolute -bottom-1 start-1/2 size-2 rounded-full bg-primary shadow-[0_0_10px_2px_rgba(99,102,241,0.7)]"></span>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ───────── Stats ───────── --}}
        <section class="border-y border-border/60 bg-card/30">
            <div class="stagger mx-auto grid max-w-7xl grid-cols-2 gap-px px-4 py-10 sm:px-6 lg:grid-cols-4 lg:px-8">
                @php
                    $stats = [
                        ['120+', 'home.stats.journeys'],
                        ['24K', 'home.stats.learners'],
                        ['380', 'home.stats.experts'],
                        ['9K', 'home.stats.hours'],
                    ];
                @endphp

                @foreach ($stats as [$value, $key])
                    <div class="px-4 text-center">
                        <p class="text-gold-gradient text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $value }}</p>
                        <p class="mt-1 text-xs font-medium text-muted-foreground sm:text-sm">{{ __($key) }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ───────── Why Madarek ───────── --}}
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="text-center text-3xl font-extrabold tracking-tight sm:text-4xl">
                {{ __('home.whyTitle') }}
            </h2>

            <div class="stagger mt-12 grid gap-6 md:grid-cols-3">
                @php
                    $reasons = [
                        ['01', 'home.why1.title', 'home.why1.desc', 'compass'],
                        ['02', 'home.why2.title', 'home.why2.desc', 'users'],
                        ['03', 'home.why3.title', 'home.why3.desc', 'telescope'],
                    ];
                @endphp

                @foreach ($reasons as [$n, $titleKey, $descKey, $icon])
                    <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-7 transition-all duration-300 hover:border-primary/30 hover:shadow-xl hover:shadow-primary/10">
                        <div class="absolute -top-4 end-4 text-6xl font-black text-border/40 transition-colors group-hover:text-primary/10">
                            {{ $n }}
                        </div>

                        @if ($icon === 'compass')
                            <svg class="size-8 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76" />
                            </svg>
                        @elseif ($icon === 'users')
                            <svg class="size-8 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        @else
                            <svg class="size-8 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6.5 6.5l11 11M4 20l4-1 11-11-3-3L5 16l-1 4zM14 4l6 6" />
                            </svg>
                        @endif

                        <h3 class="mt-5 text-lg font-bold">{{ __($titleKey) }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ __($descKey) }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ───────── CTA ───────── --}}
        <section class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-primary/20 bg-linear-to-br from-indigo-950 via-slate-950 to-indigo-900 px-6 py-16 text-center sm:px-12">
                <div class="absolute inset-0 bg-[radial-gradient(40rem_20rem_at_50%_-20%,rgba(212,175,55,0.18),transparent_70%)]"></div>

                <div class="relative">
                    <h2 class="text-balance text-3xl font-extrabold text-white sm:text-4xl">
                        {{ $dir === 'rtl' ? 'رحلتك تبدأ بخطوة واحدة' : 'Your journey starts with one step' }}
                    </h2>

                    <p class="mx-auto mt-4 max-w-xl text-sm text-indigo-200 sm:text-base">
                        {{ $dir === 'rtl'
                            ? 'انضم إلى آلاف المتعلّمين الذين يبنون مساراتهم داخل مدارك، خطوة بخطوة.'
                            : 'Join thousands of learners building their paths inside Madarek, step by step.' }}
                    </p>

                    <x-ui.button variant="gold" size="lg" href="{{ route('register') }}" class="mt-8">
                        {{ __('home.ctaPrimary') }}
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            @if ($dir === 'rtl')
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            @else
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            @endif
                        </svg>
                    </x-ui.button>
                </div>
            </div>
        </section>
    </main>

    <x-layout.public-footer />
</div>
@endsection