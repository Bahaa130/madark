@extends('layouts.workspace')

@section('title', __('builder.myJourneys'))

@section('workspace')
    <x-layout.page-header
        :title="__('builder.myJourneys')"
        :subtitle="__('builder.myJourneysSubtitle')">
        <x-slot:icon>
            <x-icon name="route" class="size-6" />
        </x-slot:icon>
        <x-slot:actions>
            <x-ui.button variant="gold" size="sm" tag="a" :href="route('instructor.journeys.create')">
                <x-icon name="sparkles" class="size-4" />
                <span>{{ __('builder.newJourney') }}</span>
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-xl border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm font-semibold text-destructive">
            {{ session('error') }}
        </div>
    @endif

    @if (count($journeys) === 0)
        <x-ui.card>
            <x-ui.card-content class="flex flex-col items-center gap-4 py-20 text-center">
                <span class="flex size-16 items-center justify-center rounded-2xl bg-secondary text-primary">
                    <x-icon name="route" class="size-8" />
                </span>
                <div>
                    <p class="font-bold">{{ __('builder.emptyTitle') }}</p>
                    <p class="mt-1 text-sm text-muted-foreground">{{ __('builder.emptySubtitle') }}</p>
                </div>
                <x-ui.button variant="gold" tag="a" :href="route('instructor.journeys.create')">
                    <x-icon name="sparkles" class="size-4" />
                    <span>{{ __('builder.newJourney') }}</span>
                </x-ui.button>
            </x-ui.card-content>
        </x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($journeys as $i => $journey)
                @php
                    $status = $journey->status?->value ?? 'DRAFT';
                    $statusVariant = match ($status) {
                        'PUBLISHED' => 'success',
                        'PENDING_REVIEW' => 'gold',
                        'ARCHIVED' => 'secondary',
                        default => 'outline',
                    };
                    $levelLabel = \App\Support\Format::level($journey->level);
                @endphp
                <div class="animate-rise" style="animation-delay: {{ number_format($i * 0.05, 2) }}s">
                    <x-ui.card class="group h-full transition-all hover:border-primary/25 hover:shadow-lg hover:shadow-primary/10">
                        <x-ui.card-content class="flex h-full flex-col p-5">
                            <div class="flex items-start justify-between gap-2">
                                @if ($journey->cover_image)
                                    <img src="{{ $journey->cover_image }}" alt=""
                                         class="h-20 w-full rounded-xl object-cover">
                                @else
                                    <div class="flex h-20 w-full items-center justify-center rounded-xl bg-linear-to-br from-primary/10 to-gold/10 text-primary">
                                        <x-icon name="route" class="size-8" />
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 flex flex-1 flex-col">
                                <div class="mb-2 flex flex-wrap items-center gap-1.5">
                                    <x-ui.badge :variant="$statusVariant" class="text-[10px]">{{ $status }}</x-ui.badge>
                                    <x-ui.badge variant="secondary" class="text-[10px]">{{ $levelLabel }}</x-ui.badge>
                                    @if ($journey->is_free)
                                        <x-ui.badge variant="gold" class="text-[10px]">{{ __('builder.free') }}</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="secondary" class="text-[10px]">${{ $journey->price }}</x-ui.badge>
                                    @endif
                                </div>

                                <p class="truncate text-sm font-bold">{{ $journey->title }}</p>
                                <p class="mt-1 line-clamp-2 text-xs text-muted-foreground">
                                    {{ $journey->tagline ?: $journey->description ?: '—' }}
                                </p>

                                <div class="mt-3 flex items-center gap-3 text-[11px] text-muted-foreground">
                                    <span class="flex items-center gap-1">
                                        <x-icon name="package" class="size-3" />
                                        {{ $journey->modules_count }} {{ __('builder.sections') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <x-icon name="file" class="size-3" />
                                        {{ $journey->lessons_count }} {{ __('common.lessons') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <x-icon name="users" class="size-3" />
                                        {{ $journey->enrollments_count }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-2 border-t border-border pt-4">
                                <x-ui.button variant="default" size="sm" class="flex-1" tag="a" :href="route('instructor.builder', $journey->id)">
                                    <x-icon name="dashboard" class="size-3.5" />
                                    <span>{{ __('builder.manage') }}</span>
                                </x-ui.button>

                                @if ($status === 'DRAFT')
                                    <form method="POST" action="{{ route('instructor.journeys.submit', $journey->id) }}"
                                          onsubmit="return confirm(__('builder.submitConfirm'))">
                                        @csrf
                                        <x-ui.button variant="outline" size="sm" type="submit" title="{{ __('builder.submitReview') }}">
                                            <x-icon name="arrow" class="size-3.5" />
                                        </x-ui.button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('instructor.journeys.destroy', $journey->id) }}"
                                      onsubmit="return confirm(__('common.confirmDelete'))">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="ghost" size="sm" type="submit" title="{{ __('common.delete') }}">
                                        <x-icon name="rotate" class="size-3.5 text-destructive" />
                                    </x-ui.button>
                                </form>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                </div>
            @endforeach
        </div>
    @endif
@endsection
