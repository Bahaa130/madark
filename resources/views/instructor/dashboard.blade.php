@extends('layouts.workspace')

@section('title', __('nav.instructor.dashboard'))

@section('workspace')
    @php
        $stats = $stats ?? [
            'journeys' => 0,
            'students' => 0,
            'avgProgress' => 0,
            'revenue' => 0,
        ];
        $journeys = $journeys ?? collect();
    @endphp

    <x-layout.page-header
        :title="__('dash.instructorTitle')"
        :subtitle="__('dash.instructorSubtitle') . ' — ' . ($instructorName ?? '')">
        <x-slot:icon>
            <x-icon name="dashboard" class="size-6" />
        </x-slot:icon>
    </x-layout.page-header>

    <div class="stagger grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $cards = [
                ['label' => __('dash.journeys'), 'value' => $stats['journeys'], 'icon' => 'route'],
                ['label' => __('dash.totalStudents'), 'value' => $stats['students'], 'icon' => 'users'],
                ['label' => __('dash.avgProgress'), 'value' => round($stats['avgProgress']) . '%', 'icon' => 'telescope'],
                ['label' => __('dash.revenue'), 'value' => $stats['revenue'], 'icon' => 'sparkles'],
            ];
        @endphp
        @foreach ($cards as $i => $card)
            <div class="animate-rise" style="animation-delay: {{ number_format($i * 0.06, 2) }}s">
                <x-ui.card>
                    <x-ui.card-content class="flex items-center gap-3 p-5">
                        <span class="flex size-11 items-center justify-center rounded-xl bg-secondary text-primary">
                            <x-icon :name="$card['icon']" class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-extrabold leading-none">{{ $card['value'] }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $card['label'] }}</p>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <h2 class="mb-4 text-lg font-bold">{{ __('dash.journeys') }}</h2>
            @if (count($journeys) === 0)
                <x-ui.card>
                    <x-ui.card-content class="py-16 text-center text-sm text-muted-foreground">
                        {{ __('common.empty') }}
                    </x-ui.card-content>
                </x-ui.card>
            @else
                <div class="space-y-3">
                    @foreach ($journeys as $i => $j)
                        <div class="animate-rise" style="animation-delay: {{ number_format($i * 0.05, 2) }}s">
                            <x-ui.card class="transition-colors hover:border-primary/25">
                                <x-ui.card-content class="p-5">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate font-bold">{{ $j['title'] }}</p>
                                            <p class="mt-1 text-xs text-muted-foreground">
                                                {{ $j['lessonsCount'] }} {{ __('common.lessons') }} · {{ $j['enrollments'] }} {{ __('common.students') }}
                                            </p>
                                        </div>
                                        @php
                                            $status = $j['status'] ?? '';
                                            $variant = 'secondary';
                                            if ($status === 'PUBLISHED') $variant = 'success';
                                            if ($status === 'PENDING_REVIEW') $variant = 'gold';
                                        @endphp
                                        <x-ui.badge :variant="$variant">{{ $status }}</x-ui.badge>
                                    </div>
                                    <div class="mt-4 flex items-center gap-3">
                                        <x-ui.progress :value="$j['progressPercent']" class="h-1.5 flex-1" />
                                        <span class="text-[11px] font-bold text-primary">{{ round($j['progressPercent']) }}%</span>
                                    </div>
                                </x-ui.card-content>
                            </x-ui.card>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="space-y-3">
            <h2 class="text-lg font-bold">{{ __('orbit.quickAccess') }}</h2>
            @php
                $quick = [
                    ['href' => route('instructor.journeys'), 'label' => __('builder.myJourneys'), 'icon' => 'route'],
                    ['href' => route('instructor.bag'), 'label' => __('dash.bagTitle'), 'icon' => 'package'],
                    ['href' => route('instructor.quizzes'), 'label' => __('quiz.title'), 'icon' => 'award'],
                    ['href' => route('instructor.council'), 'label' => __('dash.councilTitle'), 'icon' => 'messages'],
                ];
            @endphp
            @foreach ($quick as $link)
                <a href="{{ $link['href'] }}" class="group block">
                    <x-ui.card class="transition-all group-hover:border-primary/30 group-hover:shadow-lg group-hover:shadow-primary/10">
                        <x-ui.card-content class="flex items-center gap-3 p-5">
                            <span class="flex size-11 items-center justify-center rounded-xl bg-secondary text-primary">
                                <x-icon :name="$link['icon']" class="size-5" />
                            </span>
                            <span class="text-sm font-bold">{{ $link['label'] }}</span>
                        </x-ui.card-content>
                    </x-ui.card>
                </a>
            @endforeach
        </aside>
    </div>
@endsection