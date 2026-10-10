@extends('layouts.workspace')

@section('title', __('dash.adminTitle'))

@section('workspace')
    @php
        $data = $data ?? [
            'users' => collect(),
            'pendingInstructors' => collect(),
            'pendingJourneys' => collect(),
            'totals' => ['users' => 0, 'journeys' => 0, 'published' => 0],
        ];
        $levelsMap = [
            'BEGINNER' => __('levels.beginner'),
            'INTERMEDIATE' => __('levels.intermediate'),
            'ADVANCED' => __('levels.advanced'),
            'MASTER' => __('levels.master'),
        ];
    @endphp

    <x-layout.page-header
        :title="__('dash.adminTitle')"
        :subtitle="__('dash.controlCenterSubtitle')">
        <x-slot:icon>
            <x-icon name="shield-check" class="size-6" />
        </x-slot:icon>
    </x-layout.page-header>

    <div class="stagger grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $summary = [
                ['label' => __('dash.users'), 'value' => $data['totals']['users'], 'icon' => 'users'],
                ['label' => __('dash.journeys'), 'value' => $data['totals']['journeys'], 'icon' => 'book-open'],
                ['label' => 'Published', 'value' => $data['totals']['published'], 'icon' => 'shield-check'],
                ['label' => __('dash.pendingInstructors'), 'value' => count($data['pendingInstructors']), 'icon' => 'user-check'],
            ];
        @endphp
        @foreach ($summary as $i => $c)
            <div class="animate-rise" style="animation-delay: {{ number_format($i * 0.06, 2) }}s">
                <x-ui.card>
                    <x-ui.card-content class="flex items-center gap-3 p-5">
                        <span class="flex size-11 items-center justify-center rounded-xl bg-secondary text-primary">
                            <x-icon :name="$c['icon']" class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-extrabold leading-none">{{ $c['value'] }}</p>
                            <p class="mt-1 line-clamp-1 text-xs text-muted-foreground">{{ $c['label'] }}</p>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section>
            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold">
                <x-icon name="user-check" class="size-5 text-primary" />
                {{ __('dash.pendingInstructors') }}
                <x-ui.badge variant="secondary" class="ms-1">{{ count($data['pendingInstructors']) }}</x-ui.badge>
            </h2>
            @if (count($data['pendingInstructors']) === 0)
                <x-ui.card>
                    <x-ui.card-content class="py-14 text-center text-sm text-muted-foreground">{{ __('common.empty') }}</x-ui.card-content>
                </x-ui.card>
            @else
                <div class="space-y-3">
                    @foreach ($data['pendingInstructors'] as $i => $p)
                        <div class="animate-rise" style="animation-delay: {{ number_format($i * 0.05, 2) }}s">
                            <x-ui.card>
                                <x-ui.card-content class="flex flex-wrap items-center gap-4 p-4">
                                    @php $in = $p['user'] ?? []; $ini = mb_strtoupper(mb_substr((string) ($in['name'] ?? '?'), 0, 1)); @endphp
                                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary to-indigo-900 text-sm font-bold text-primary-foreground">
                                        {{ $ini ?: '?' }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold">{{ $in['name'] ?? '' }}</p>
                                        <p class="truncate text-xs text-muted-foreground">{{ $in['email'] ?? '' }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.verifyInstructor', $p['id']) }}">
                                            @csrf
                                            <x-ui.button variant="outline" size="sm" type="submit" class="text-emerald-600">
                                                <x-icon name="check" class="size-4" />
                                                <span>{{ __('dash.verify') }}</span>
                                            </x-ui.button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.rejectInstructor', $p['id']) }}">
                                            @csrf
                                            <x-ui.button variant="ghost" size="sm" type="submit" class="text-destructive">
                                                <x-icon name="x" class="size-4" />
                                                <span>{{ __('dash.reject') }}</span>
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </x-ui.card-content>
                            </x-ui.card>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section>
            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold">
                <x-icon name="book-open" class="size-5 text-primary" />
                {{ __('dash.pendingJourneys') }}
                <x-ui.badge variant="gold" class="ms-1">{{ count($data['pendingJourneys']) }}</x-ui.badge>
            </h2>
            @if (count($data['pendingJourneys']) === 0)
                <x-ui.card>
                    <x-ui.card-content class="py-14 text-center text-sm text-muted-foreground">{{ __('common.empty') }}</x-ui.card-content>
                </x-ui.card>
            @else
                <div class="space-y-3">
                    @foreach ($data['pendingJourneys'] as $i => $j)
                        <div class="animate-rise" style="animation-delay: {{ number_format($i * 0.05, 2) }}s">
                            <x-ui.card>
                                <x-ui.card-content class="flex flex-wrap items-center gap-4 p-4">
                                    @php $ji = $j['instructor'] ?? []; $ini = mb_strtoupper(mb_substr((string) ($ji['name'] ?? '?'), 0, 1)); @endphp
                                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary to-indigo-900 text-sm font-bold text-primary-foreground">
                                        {{ $ini ?: '?' }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold">{{ $j['title'] }}</p>
                                        <p class="truncate text-xs text-muted-foreground">{{ $ji['name'] ?? '' }} · {{ $j['modulesCount'] ?? 0 }} modules</p>
                                    </div>
                                    <x-ui.badge variant="secondary">{{ $levelsMap[$j['level'] ?? ''] ?? ($j['level'] ?? '') }}</x-ui.badge>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.approveJourney', $j['id']) }}">
                                            @csrf
                                            <x-ui.button variant="outline" size="sm" type="submit" class="text-emerald-600">
                                                <x-icon name="badge-check" class="size-4" />
                                                <span>{{ __('dash.approve') }}</span>
                                            </x-ui.button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.rejectJourney', $j['id']) }}">
                                            @csrf
                                            <x-ui.button variant="ghost" size="sm" type="submit" class="text-destructive">
                                                <x-icon name="x" class="size-4" />
                                                <span>{{ __('dash.reject') }}</span>
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </x-ui.card-content>
                            </x-ui.card>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="mt-8">
        <h2 class="mb-4 flex items-center gap-2 text-lg font-bold">
            <x-icon name="users" class="size-5 text-primary" />
            {{ __('dash.users') }}
        </h2>
        <x-ui.card>
            <x-ui.card-content class="p-0">
                <ul class="divide-y divide-border">
                    @foreach ($data['users'] as $u)
                        <li class="flex items-center gap-4 px-5 py-3.5">
                            @php $uni = mb_strtoupper(mb_substr((string) ($u['name'] ?? '?'), 0, 1)); @endphp
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary to-indigo-900 text-sm font-bold text-primary-foreground">
                                {{ $uni ?: '?' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $u['name'] ?? '' }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ $u['email'] ?? '' }}</p>
                            </div>
                            @php
                                $r = $u['role'] ?? '';
                                $rv = 'secondary';
                                if ($r === 'ADMIN') $rv = 'destructive';
                                if ($r === 'INSTRUCTOR') $rv = 'gold';
                            @endphp
                            <x-ui.badge :variant="$rv">{{ $r }}</x-ui.badge>
                            <span class="hidden text-xs text-muted-foreground sm:block">
                                {{ $u['enrollmentsCount'] ?? 0 }} {{ __('nav.student.paths') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card-content>
        </x-ui.card>
    </section>
@endsection