@extends('layouts.workspace')

@section('title', __('nav.student.orbit'))

@section('workspace')
    @php
        $user = auth()->user();
        $firstName = \Illuminate\Support\Str::before((string) $user->name, ' ');
    @endphp

    <x-layout.page-header
        :title="__('common.welcome') . '، ' . $firstName . ' ✦'"
        :subtitle="__('orbit.subtitle')">
        <x-slot:icon>
            <x-icon name="orbit" class="size-6" />
        </x-slot:icon>

        <x-slot:actions>
            <a href="{{ route('explore') }}">
                <x-ui.badge variant="gold" class="cursor-pointer gap-1.5 px-3 py-1.5">
                    <svg class="size-3" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l1.9 5.6L19.5 9.5 13.9 11.4 12 17l-1.9-5.6L4.5 9.5l5.6-1.9L12 2z" />
                    </svg>
                    {{ __('common.start') }}
                </x-ui.badge>
            </a>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- ───────── Stats ───────── --}}
    @php
        $statCards = [
            ['label' => __('orbit.stats.activePaths'), 'value' => $stats['activePaths'], 'icon' => 'route', 'tint' => 'from-primary/15'],
            ['label' => __('orbit.stats.lessonsDone'), 'value' => $stats['lessonsDone'], 'icon' => 'orbit', 'tint' => 'from-emerald-500/15'],
            ['label' => __('orbit.stats.certificates'), 'value' => $stats['certificates'], 'icon' => 'compass', 'tint' => 'from-gold/20'],
            ['label' => __('orbit.stats.streak'), 'value' => $stats['streak'] . 'd', 'icon' => 'lightbulb', 'tint' => 'from-orange-500/15'],
        ];
    @endphp

    <div class="stagger grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($statCards as $stat)
            <div>
                <x-ui.card class="overflow-hidden">
                    <div class="h-1 w-full bg-linear-to-l {{ $stat['tint'] }} to-transparent"></div>

                    <x-ui.card-content class="flex items-center gap-3 p-5">
                        <span class="flex size-11 items-center justify-center rounded-xl bg-secondary text-primary">
                            <x-icon :name="$stat['icon']" class="size-5" />
                        </span>

                        <div>
                            <p class="text-2xl font-extrabold leading-none">{{ $stat['value'] }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $stat['label'] }}</p>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        {{-- ───────── Continue + quick access ───────── --}}
        <div class="space-y-6 lg:col-span-2">
            @if ($continueJourney)
                <section>
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-bold">
                        <svg class="size-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 7l-8.5 8.5-5-5L2 17" />
                            <path d="M16 7h6v6" />
                        </svg>
                        {{ __('orbit.continueLearning') }}
                    </h2>

                    <x-journey.journey-card :journey="$continueJourney" />
                </section>
            @else
                <x-ui.card>
                    <x-ui.card-content class="flex flex-col items-center gap-3 p-10 text-center">
                        <span class="flex size-14 items-center justify-center rounded-2xl bg-secondary text-primary">
                            <x-icon name="orbit" class="size-7" />
                        </span>

                        <p class="text-sm text-muted-foreground">{{ __('paths.empty') }}</p>

                        <a href="{{ route('explore') }}" class="text-sm font-bold text-primary hover:underline">
                            {{ __('paths.browse') }} →
                        </a>
                    </x-ui.card-content>
                </x-ui.card>
            @endif

            {{-- Quick access --}}
            @php
                $quickLinks = [
                    ['href' => route('student.paths'), 'label' => __('nav.student.paths'), 'icon' => 'route'],
                    ['href' => route('student.cosmos'), 'label' => __('nav.student.cosmos'), 'icon' => 'telescope'],
                    ['href' => route('student.lighthouse'), 'label' => __('nav.student.lighthouse'), 'icon' => 'lightbulb'],
                    ['href' => route('student.compass'), 'label' => __('nav.student.compass'), 'icon' => 'compass'],
                    ['href' => route('student.profile'), 'label' => __('nav.student.profile'), 'icon' => 'user'],
                ];
            @endphp

            <section>
                <h2 class="mb-4 text-lg font-bold">{{ __('orbit.quickAccess') }}</h2>

                <div class="stagger grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['href'] }}" class="group block">
                            <x-ui.card class="h-full transition-all group-hover:border-primary/30 group-hover:shadow-lg group-hover:shadow-primary/10">
                                <x-ui.card-content class="flex flex-col items-center gap-2 p-5 text-center">
                                    <x-icon :name="$link['icon']" class="size-6 text-primary transition-transform group-hover:scale-110" />
                                    <span class="text-xs font-bold">{{ $link['label'] }}</span>
                                </x-ui.card-content>
                            </x-ui.card>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- ───────── Madarek Pulse ───────── --}}
        <aside>
            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold">
                <span class="relative flex size-2.5">
                    <span class="absolute inline-flex size-full animate-ping rounded-full bg-gold opacity-70"></span>
                    <span class="relative inline-flex size-2.5 rounded-full bg-gold"></span>
                </span>
                {{ __('orbit.notifications') }}
            </h2>

            <x-ui.card>
                <x-ui.card-content class="p-4">
                    @if ($notifications->isEmpty())
                        <p class="py-8 text-center text-sm text-muted-foreground">{{ __('orbit.emptyFeed') }}</p>
                    @else
                        <ul class="space-y-1">
                            @foreach ($notifications as $notification)
                                <li>
                                    <a href="{{ $notification->url ?? '#' }}"
                                       class="flex gap-3 rounded-xl p-2.5 transition-colors hover:bg-secondary/60">
                                        <span @class([
                                            'mt-1.5 size-2 shrink-0 rounded-full',
                                            'bg-transparent' => $notification->read,
                                            'bg-primary' => ! $notification->read,
                                        ])></span>

                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold">{{ $notification->title }}</p>

                                            @if ($notification->body)
                                                <p class="line-clamp-2 text-xs text-muted-foreground">{{ $notification->body }}</p>
                                            @endif
                                        </div>

                                        <svg class="mt-0.5 size-3.5 shrink-0 text-muted-foreground"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                        </svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card-content>
            </x-ui.card>
        </aside>
    </div>
@endsection