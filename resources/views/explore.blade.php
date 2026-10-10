@extends('layouts.app')

@section('title', __('explore.title'))

@section('content')
<div class="min-h-dvh cosmic-bg">
    <x-layout.public-header />

    <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="animate-rise mb-10 text-center">
            <span class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-linear-to-br from-primary to-indigo-900 text-white shadow-lg shadow-primary/25">
                {{-- Telescope --}}
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6.5 6.5l11 11M4 20l4-1 11-11-3-3L5 16l-1 4zM14 4l6 6" />
                </svg>
            </span>

            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('explore.title') }}</h1>

            <p class="mx-auto mt-3 max-w-xl text-sm text-muted-foreground sm:text-base">
                {{ __('explore.subtitle') }}
            </p>
        </div>

        {{-- One Alpine scope wraps the filter and the grid so the selected
             level drives both, mirroring the reference useState. --}}
        <div x-data="{ level: '{{ $levels[0] }}' }">
            {{-- Level filter --}}
            <div class="mb-8 flex flex-wrap items-center justify-center gap-2">
                @foreach ($levels as $level)
                    @php
                        $value = $level;
                        $label = $level === 'ALL' ? __('explore.all') : __('levels.' . $level);
                    @endphp

                    <button type="button"
                            @click="level = '{{ $value }}'"
                            :class="level === '{{ $value }}'
                                ? 'text-primary-foreground'
                                : 'text-muted-foreground hover:bg-secondary hover:text-foreground'"
                            class="relative rounded-full px-4 py-2 text-sm font-semibold transition-colors">
                        <span class="absolute inset-0 rounded-full bg-primary shadow-lg shadow-primary/25 transition-transform duration-300"
                              x-show="level === '{{ $value }}'"
                              x-cloak></span>
                        <span class="relative z-10">{{ $label }}</span>
                    </button>
                @endforeach
            </div>

            @if ($journeys->isEmpty())
                <div class="flex flex-col items-center gap-4 py-24 text-center">
                    <span class="flex size-16 items-center justify-center rounded-2xl bg-secondary text-muted-foreground">
                        {{-- Compass --}}
                        <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76" />
                        </svg>
                    </span>
                    <p class="text-sm text-muted-foreground">{{ __('explore.empty') }}</p>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($journeys as $index => $journey)
                        <div x-show="level === 'ALL' || level === '{{ $journey->level->value }}'" x-cloak>
                            <x-journey.journey-card :journey="$journey" :index="$index" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <x-layout.public-footer />
</div>
@endsection