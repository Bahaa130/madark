@extends('layouts.workspace')

@section('title', __('lighthouse.title'))

@section('workspace')
    <x-layout.page-header :title="__('lighthouse.title')" :subtitle="__('lighthouse.subtitle')">
        <x-slot:icon>
            <x-icon name="lightbulb" class="size-6" />
        </x-slot:icon>

        <x-slot:actions>
            <x-ui.badge variant="gold" class="gap-1.5 px-3 py-1.5">
                <x-icon name="verified" class="size-3" />
                {{ $instructors->where('verified', true)->count() }} {{ __('lighthouse.experts') }}
            </x-ui.badge>
        </x-slot:actions>
    </x-layout.page-header>

    @if ($instructors->isEmpty())
        <x-ui.card>
            <x-ui.card-content class="flex flex-col items-center gap-3 py-16 text-center">
                <span class="flex size-14 items-center justify-center rounded-2xl bg-secondary text-muted-foreground">
                    <x-icon name="lightbulb" class="size-6" />
                </span>

                <p class="text-sm text-muted-foreground">{{ __('common.empty') }}</p>
            </x-ui.card-content>
        </x-ui.card>
    @else
        <div class="stagger grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($instructors as $instructor)
                <x-ui.card class="group h-full transition-all hover:border-primary/25 hover:shadow-xl hover:shadow-primary/10">
                    <x-ui.card-content class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="relative shrink-0">
                                @if ($instructor['image'])
                                    <img src="{{ $instructor['image'] }}" alt="{{ $instructor['name'] }}"
                                         class="size-14 rounded-full object-cover">
                                @else
                                    <span class="flex size-14 items-center justify-center rounded-full bg-linear-to-br from-primary to-indigo-900 text-base font-bold text-primary-foreground">
                                        {{ mb_strtoupper(mb_substr($instructor['name'], 0, 1)) }}
                                    </span>
                                @endif

                                @if ($instructor['verified'])
                                    <span class="absolute -bottom-0.5 -end-0.5 flex size-5 items-center justify-center rounded-full bg-primary text-white ring-2 ring-card">
                                        <x-icon name="verified" class="size-3" />
                                    </span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold">{{ $instructor['name'] }}</p>

                                @if ($instructor['headline'])
                                    <p class="truncate text-xs text-primary">{{ $instructor['headline'] }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($instructor['bio'])
                            <p class="mt-4 line-clamp-3 text-sm leading-relaxed text-muted-foreground">
                                {{ $instructor['bio'] }}
                            </p>
                        @endif

                        <div class="mt-5 flex items-center gap-4 text-xs text-muted-foreground">
                            <span class="flex items-center gap-1.5">
                                <x-icon name="users" class="size-3.5" />
                                {{ $instructor['journeysCount'] }} {{ __('lighthouse.journeys') }}
                            </span>

                            @if ($instructor['verified'])
                                <x-ui.badge variant="success" class="ms-auto">
                                    {{ __('lighthouse.verified') }}
                                </x-ui.badge>
                            @endif
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            @endforeach
        </div>
    @endif
@endsection