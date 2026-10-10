@extends('layouts.workspace')

@section('title', __('nav.student.paths'))

@section('workspace')
<x-layout.page-header
        :title="__('paths.title')"
        :subtitle="__('paths.subtitle')">
        <x-slot:icon>
            <x-icon name="route" class="size-6" />
        </x-slot:icon>

    @if ($active->isEmpty() && $completed->isEmpty())
        <x-ui.card>
            <x-ui.card-content class="flex flex-col items-center gap-3 p-12 text-center">
                <span class="flex size-14 items-center justify-center rounded-2xl bg-secondary text-muted-foreground">
                    <x-icon name="route" class="size-7" />
                </span>

                <p class="text-sm text-muted-foreground">{{ __('paths.empty') }}</p>

                <a href="{{ route('explore') }}" class="text-sm font-bold text-primary hover:underline">
                    {{ __('paths.browse') }} →
                </a>
            </x-ui.card-content>
        </x-ui.card>
    @else
        @foreach ([__('paths.active') => $active, __('paths.completed') => $completed] as $heading => $group)
            @if ($group->isNotEmpty())
                <section class="mb-8">
                    <h2 class="mb-4 text-lg font-bold">{{ $heading }}</h2>

                    <div class="stagger grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($group as $index => $journey)
                            <x-journey.journey-card :journey="$journey" :index="$index" />
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
@endif
</x-layout.page-header>
@endsection
