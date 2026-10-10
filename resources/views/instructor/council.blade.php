@extends('layouts.workspace')

@section('title', __('dash.councilTitle'))

@section('workspace')
    @php
        $threads = $threads ?? collect();
    @endphp

    <x-layout.page-header
        :title="__('dash.councilTitle')"
        :subtitle="__('dash.councilSubtitle')">
        <x-slot:icon>
            <x-icon name="messages-square" class="size-6" />
        </x-slot:icon>
        <x-slot:actions>
            <x-ui.button variant="gold" size="sm">
                <x-icon name="plus" class="size-4" />
                <span>{{ __('dash.newThread') }}</span>
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    @if (empty($threads) || count($threads) === 0)
        <x-ui.card>
            <x-ui.card-content class="flex flex-col items-center gap-4 py-20 text-center">
                <span class="flex size-16 items-center justify-center rounded-2xl bg-secondary text-primary">
                    <x-icon name="messages-square" class="size-8" />
                </span>
                <p class="text-sm text-muted-foreground">{{ __('common.empty') }}</p>
            </x-ui.card-content>
        </x-ui.card>
    @else
        <div class="space-y-3">
            @foreach ($threads as $i => $th)
                <div class="animate-rise" style="animation-delay: {{ number_format($i * 0.05, 2) }}s">
                    <x-ui.card class="transition-colors hover:border-primary/25">
                        <x-ui.card-content class="flex items-start gap-4 p-5">
                            @php
                                $initial = mb_strtoupper(mb_substr((string) ($th['author']['name'] ?? '?'), 0, 1));
                            @endphp
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary to-indigo-900 text-sm font-bold text-primary-foreground">
                                {{ $initial ?: '?' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if (! empty($th['pinned']))
                                        <x-ui.badge variant="gold" class="gap-1">
                                            <x-icon name="pin" class="size-3" />
                                            <span>pinned</span>
                                        </x-ui.badge>
                                    @endif
                                    @if (! empty($th['locked']))
                                        <x-ui.badge variant="secondary" class="gap-1">
                                            <x-icon name="shield" class="size-3" />
                                            <span>locked</span>
                                        </x-ui.badge>
                                    @endif
                                    @if (! empty($th['journey']['title']))
                                        <x-ui.badge>{{ $th['journey']['title'] }}</x-ui.badge>
                                    @endif
                                </div>
                                <p class="mt-2 font-bold leading-snug">{{ $th['title'] }}</p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ $th['author']['name'] ?? '' }} · {{ $th['postsCount'] ?? 0 }} {{ __('dash.councilTitle') }} · {{ \App\Support\Format::date($th['updatedAt'] ?? null, app()->getLocale()) }}
                                </p>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                </div>
            @endforeach
        </div>
    @endif
@endsection