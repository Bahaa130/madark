@extends('layouts.workspace')

@section('title', $quiz->title)

@section('workspace')
    @php
        $attempt = session('attempt');
    @endphp

    <x-layout.page-header
        :title="$quiz->title"
        :subtitle="$quiz->journey?->title">
        <x-slot:icon>
            <x-icon name="award" class="size-6" />
        </x-slot:icon>
        <x-slot:actions>
            <x-ui.button variant="ghost" size="sm" tag="a" :href="route('student.journey.show', $quiz->journey->slug)">
                <x-icon name="arrow" class="size-4" />
                <span>{{ __('common.back') }}</span>
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- ───────── Result banner ───────── --}}
    @if ($attempt)
        <div class="mb-6 animate-rise rounded-2xl border p-5
                    {{ $attempt['passed']
                        ? 'border-emerald-500/30 bg-emerald-500/10'
                        : 'border-destructive/30 bg-destructive/10' }}">
            <div class="flex items-center gap-4">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl
                            {{ $attempt['passed'] ? 'bg-emerald-500/15 text-emerald-500' : 'bg-destructive/15 text-destructive' }}">
                    <x-icon name="trophy" class="size-7" />
                </span>
                <div class="flex-1">
                    <p class="text-lg font-extrabold">
                        {{ $attempt['passed'] ? __('quiz.passed') : __('quiz.failed') }}
                    </p>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        {{ __('quiz.score') }}: <span class="font-bold text-foreground">{{ $attempt['score'] }}%</span>
                        ({{ $attempt['correct'] }} / {{ $attempt['total'] }} {{ __('quiz.questions') }})
                    </p>
                </div>
                <div class="text-3xl font-black {{ $attempt['passed'] ? 'text-emerald-500' : 'text-destructive' }}">
                    {{ $attempt['score'] }}%
                </div>
            </div>
        </div>
    @endif

    {{-- ───────── Previous attempts ───────── --}}
    @if (count($attempts) > 0)
        <div class="mb-6 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-muted-foreground">{{ __('quiz.previousAttempts') }}:</span>
            @foreach ($attempts as $a)
                <x-ui.badge :variant="$a->passed ? 'success' : 'destructive'" class="text-[10px]">
                    {{ $a->score }}%
                </x-ui.badge>
            @endforeach
        </div>
    @endif

    <div class="mx-auto max-w-3xl">
        <x-ui.card>
            <x-ui.card-content class="p-6 sm:p-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-extrabold">{{ $quiz->title }}</h1>
                        @if ($quiz->description)
                            <p class="mt-1 text-sm text-muted-foreground">{{ $quiz->description }}</p>
                        @endif
                    </div>
                    <x-ui.badge variant="gold">
                        {{ __('quiz.passingScore') }}: {{ $quiz->passing_score }}%
                    </x-ui.badge>
                </div>

                <form method="POST" action="{{ route('student.quiz.submit', $quiz->id) }}" class="space-y-6">
                    @csrf

                    @foreach ($quiz->questions as $qIndex => $question)
                        <div class="rounded-2xl border border-border/70 bg-secondary/20 p-4">
                            <p class="mb-3 flex items-start gap-2 text-sm font-bold">
                                <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-card text-[11px] font-black text-primary ring-1 ring-border ring-inset">
                                    {{ $qIndex + 1 }}
                                </span>
                                {{ $question->text }}
                            </p>

                            <div class="space-y-2 ps-8">
                                @foreach ($question->options as $option)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border px-3 py-2.5 text-sm transition-all
                                                hover:border-primary/30 hover:bg-secondary
                                                {{ old('answers.'.$question->id) == $option->id ? 'border-primary/50 bg-primary/5' : 'border-border bg-card/60' }}">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}"
                                               @checked(old('answers.'.$question->id) == $option->id)
                                               class="size-4 shrink-0 accent-indigo-600">
                                        <span>{{ $option->text }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <x-ui.button type="submit" variant="gold" class="w-full">
                        <x-icon name="trophy" class="size-4" />
                        <span>{{ __('quiz.submitAnswers') }}</span>
                    </x-ui.button>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
@endsection
