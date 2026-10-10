@extends('layouts.workspace')

@section('title', __('compass.title'))

@section('workspace')
    @php
        $resultLabel = __('compass.result');
        $questionLabel = __('compass.question');
        $ofLabel = __('compass.of');

        $forward = $dir === 'rtl' ? 'rotate-180' : '';
        $backward = $dir === 'rtl' ? '' : 'rotate-180';

        $payload = $recommendations
            ->map(fn ($journey) => [
                'title' => $journey['title'],
                'tagline' => $journey['tagline'],
                'url' => route('student.journey.show', $journey['slug']),
            ])
            ->values();
    @endphp

    <x-layout.page-header :title="__('compass.title')" :subtitle="__('compass.subtitle')">
        <x-slot:icon>
            <x-icon name="compass" class="size-6" />
        </x-slot:icon>
    </x-layout.page-header>

    @if ($questions->isEmpty() || $payload->isEmpty())
        <x-ui.card>
            <x-ui.card-content class="flex flex-col items-center gap-3 py-16 text-center">
                <span class="flex size-14 items-center justify-center rounded-2xl bg-secondary text-muted-foreground">
                    <x-icon name="compass" class="size-6" />
                </span>

                <p class="text-sm text-muted-foreground">{{ __('common.empty') }}</p>

                <a href="{{ route('explore') }}" class="text-sm font-bold text-primary hover:underline">
                    {{ __('paths.browse') }}
                </a>
            </x-ui.card-content>
        </x-ui.card>
    @else
        <div class="mx-auto max-w-3xl"
             x-data="compass({{ \Illuminate\Support\Js::from($questions) }}, {{ \Illuminate\Support\Js::from($payload) }})">

            <x-ui.card class="overflow-hidden">
                <div class="border-b border-border bg-card/50 px-6 py-4">
                    <div class="mb-2 flex items-center justify-between text-xs font-bold text-muted-foreground">
                        <span x-text="done
                            ? @js($resultLabel)
                            : @js($questionLabel) + ' ' + (step + 1) + ' ' + @js($ofLabel) + ' ' + total"
                              x-cloak>{{ $questionLabel }} 1 {{ $ofLabel }} {{ count($questions) }}</span>

                        <span x-text="Math.round(progress) + '%'">0%</span>
                    </div>

                    <div class="relative h-1.5 w-full overflow-hidden rounded-full bg-secondary">
                        <div class="h-full rounded-full bg-linear-to-l from-gold to-gold-soft transition-all duration-500 ease-out"
                             :style="`width: ${progress}%`"
                             style="width: 0%"></div>
                    </div>
                </div>

                <x-ui.card-content class="p-6 sm:p-8">
                    @foreach ($questions as $index => $question)
                        <div x-show="! done && step === {{ $index }}" x-transition.opacity.duration.200ms
                             x-cloak>
                            <h2 class="text-lg font-bold leading-relaxed sm:text-xl">{{ $question['text'] }}</h2>

                            <div class="mt-6 space-y-2.5">
                                @foreach ($question['options'] as $optionIndex => $option)
                                    <button type="button"
                                            @click="select(@js($question['id']), @js($option['id']))"
                                            :class="isSelected(@js($question['id']), @js($option['id']))
                                                ? 'border-primary bg-primary/10 text-primary shadow-sm'
                                                : 'border-border hover:border-primary/40 hover:bg-secondary/50'"
                                            class="flex w-full items-center gap-3 rounded-xl border px-4 py-3.5 text-start text-sm font-semibold transition-all duration-200">
                                        <span class="flex size-7 shrink-0 items-center justify-center rounded-lg text-xs font-black transition-colors"
                                              :class="isSelected(@js($question['id']), @js($option['id']))
                                                  ? 'bg-primary text-primary-foreground'
                                                  : 'bg-secondary text-muted-foreground'">
                                            {{ chr(65 + $optionIndex) }}
                                        </span>

                                        <span x-text="questions[{{ $index }}].options[{{ $optionIndex }}].text">{{ $option['text'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-7 flex items-center justify-between gap-3" x-show="! done" x-cloak>
                        <x-ui.button variant="ghost" @click="back()" ::disabled="step === 0">
                            <x-icon name="arrow" class="size-4 {{ $backward }}" />
                            {{ __('common.back') }}
                        </x-ui.button>

                        <x-ui.button @click="next()" ::disabled="! answered">
                            <span x-text="isLast ? @js($resultLabel) : @js(__('common.continue'))">{{ __('common.continue') }}</span>
                            <x-icon name="arrow" class="size-4 {{ $forward }}" />
                        </x-ui.button>
                    </div>

                    <div class="text-center" x-show="done" x-cloak>
                        <span class="mx-auto mb-4 flex size-16 items-center justify-center rounded-2xl bg-linear-to-br from-gold/25 to-gold/5 text-gold ring-1 ring-gold/20 ring-inset">
                            <x-icon name="sparkles" class="size-8" />
                        </span>

                        <h2 class="text-2xl font-extrabold">{{ $resultLabel }}</h2>
                        <p class="mt-2 text-sm text-muted-foreground">{{ __('compass.matches') }}</p>

                        <ul class="mt-6 space-y-3 text-start">
                            <template x-for="match in matched" :key="match.url">
                                <li>
                                    <a :href="match.url" class="group block">
                                        <x-ui.card class="transition-all hover:border-primary/30 hover:shadow-lg hover:shadow-primary/10">
                                            <x-ui.card-content class="flex items-center justify-between gap-4 p-4">
                                                <div class="min-w-0">
                                                    <p class="truncate font-bold transition-colors group-hover:text-primary"
                                                       x-text="match.title"></p>

                                                    <p class="truncate text-xs text-muted-foreground"
                                                       x-text="match.tagline"
                                                       x-show="match.tagline"></p>
                                                </div>

                                                <x-icon name="arrow" class="size-4 shrink-0 text-primary {{ $forward }}" />
                                            </x-ui.card-content>
                                        </x-ui.card>
                                    </a>
                                </li>
                            </template>
                        </ul>

                        <div class="mt-7 flex justify-center">
                            <x-ui.button variant="outline" @click="restart()">
                                <x-icon name="rotate" class="size-4" />
                                {{ __('compass.restart') }}
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    @endif
@endsection
