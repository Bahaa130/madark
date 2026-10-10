@props([
    'journey',
    // Stagger index for the entrance animation; optional when the card is
    // rendered on its own (e.g. the "continue learning" card).
    'index' => 0,
])

@php
    use App\Support\Format;

    $journey = $journey ?? new class {};
    $href = route('student.journey.show', $journey->slug ?: $journey->id);
    $progress = $journey->progress_percent ?? null;
    $levelLabel = Format::level($journey->level ?? null);
@endphp

<div class="animate-rise" style="animation-delay: {{ number_format($index * 0.06, 2) }}s">
    <a href="{{ $href }}" class="group block h-full">
        <x-ui.card class="h-full overflow-hidden hover:shadow-xl hover:shadow-primary/10 hover:ring-1 hover:ring-primary/20">
            {{-- Cover --}}
            <div class="relative aspect-16/9 overflow-hidden bg-linear-to-br from-indigo-950 via-indigo-900 to-slate-900">
                @if (! empty($journey->cover_image))
                    <img src="{{ $journey->cover_image }}" alt="{{ $journey->title }}"
                         class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                @else
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="flex size-16 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10 backdrop-blur transition-transform duration-500 group-hover:scale-110">
                            {{-- Play --}}
                            <svg class="size-7 text-white/80" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l11.14-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14z" />
                            </svg>
                        </div>
                    </div>
                @endif

                <div class="absolute inset-0 bg-linear-to-t from-black/50 via-transparent to-transparent"></div>

                @if ($levelLabel)
                    <x-ui.badge variant="gold" class="absolute top-3 start-3 shadow-lg">
                        {{ $levelLabel }}
                    </x-ui.badge>
                @endif

                @if (is_numeric($progress))
                    <div class="absolute inset-x-3 bottom-3">
                        <div class="mb-1.5 flex items-center justify-between text-[11px] font-semibold text-white/90">
                            <span>{{ __('common.myProgress') }}</span>
                            <span class="text-gold-soft">{{ round((float) $progress) }}%</span>
                        </div>

                        <x-ui.progress :value="$progress" class="h-1.5 bg-white/15"
                                       indicator-class="bg-linear-to-l from-gold to-gold-soft" />
                    </div>
                @endif
            </div>

            <x-ui.card-content class="p-5">
                <h3 class="line-clamp-1 font-bold transition-colors group-hover:text-primary">
                    {{ $journey->title }}
                </h3>

                @if (! empty($journey->tagline))
                    <p class="mt-1 line-clamp-2 min-h-10 text-sm text-muted-foreground">
                        {{ $journey->tagline }}
                    </p>
                @endif

                <div class="mt-4 flex items-center gap-4 text-xs text-muted-foreground">
                    @if (isset($journey->lessons_count))
                        <span class="flex items-center gap-1">
                            {{-- BookOpen --}}
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                            </svg>
                            {{ $journey->lessons_count }} {{ __('common.lessons') }}
                        </span>
                    @endif

                    @if (isset($journey->students_count))
                        <span class="flex items-center gap-1">
                            {{-- Users --}}
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            </svg>
                            {{ $journey->students_count }}
                        </span>
                    @endif

                    @if (! empty($journey->duration_min))
                        <span class="flex items-center gap-1">
                            {{-- Clock --}}
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 6v6l4 2" />
                            </svg>
                            {{ Format::duration((int) $journey->duration_min) }}
                        </span>
                    @endif
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </a>
</div>