@extends('layouts.workspace')

@section('title', __('quiz.title'))

@section('workspace')
    <x-layout.page-header
        :title="__('quiz.title')"
        :subtitle="__('quiz.subtitle')">
        <x-slot:icon>
            <x-icon name="award" class="size-6" />
        </x-slot:icon>
    </x-layout.page-header>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_420px]">
        {{-- ───────── Existing quizzes ───────── --}}
        <div class="min-w-0 space-y-3">
            <h2 class="text-lg font-bold">{{ __('quiz.existing') }}</h2>

            @if (count($quizzes) === 0)
                <x-ui.card>
                    <x-ui.card-content class="py-16 text-center text-sm text-muted-foreground">
                        {{ __('common.empty') }}
                    </x-ui.card-content>
                </x-ui.card>
            @else
                @foreach ($quizzes as $i => $quiz)
                    <div class="animate-rise" style="animation-delay: {{ number_format($i * 0.05, 2) }}s">
                        <x-ui.card class="transition-all hover:border-primary/25">
                            <x-ui.card-content class="p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-bold">{{ $quiz->title }}</p>
                                        <p class="mt-1 text-xs text-muted-foreground">
                                            {{ $quiz->journey?->title }} · {{ $quiz->questions_count }} {{ __('quiz.questions') }}
                                            · {{ __('quiz.passingScore') }}: {{ $quiz->passing_score }}%
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-ui.badge variant="secondary">{{ $quiz->attempts_count }} {{ __('quiz.attempts') }}</x-ui.badge>
                                        <form method="POST" action="{{ route('instructor.quizzes.destroy', $quiz->id) }}"
                                              onsubmit="return confirm(__('common.confirmDelete'))">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="ghost" size="icon-sm" type="submit" title="{{ __('common.delete') }}">
                                                <x-icon name="rotate" class="size-4 text-destructive" />
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </div>
                            </x-ui.card-content>
                        </x-ui.card>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- ───────── Quiz maker ───────── --}}
        <div class="min-w-0">
            <div class="lg:sticky lg:top-24">
                <x-ui.card>
                    <x-ui.card-content class="p-5"
                        x-data="quizMaker()">
                        <h3 class="mb-4 text-sm font-bold">{{ __('quiz.maker') }}</h3>

                        <form method="POST" action="{{ route('instructor.quizzes.store') }}" class="space-y-4">
                            @csrf

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-muted-foreground">{{ __('builder.journey') }} *</label>
                                <select name="journey_id" required
                                        class="h-11 w-full rounded-xl border border-input bg-card px-3 text-sm shadow-sm">
                                    <option value="">— {{ __('quiz.selectJourney') }} —</option>
                                    @foreach ($journeys as $j)
                                        <option value="{{ $j->id }}">{{ $j->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-muted-foreground">{{ __('quiz.quizTitle') }} *</label>
                                <x-ui.input name="title" required minlength="3" maxlength="190"
                                            placeholder="{{ __('quiz.quizTitlePlaceholder') }}" />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-muted-foreground">{{ __('quiz.passingScore') }}</label>
                                <x-ui.input name="passing_score" type="number" min="1" max="100" value="60" />
                            </div>

                            {{-- Questions --}}
                            <div class="space-y-3">
                                <template x-for="(question, qIndex) in questions" :key="qIndex">
                                    <div class="rounded-xl border border-border bg-secondary/20 p-3">
                                        <div class="mb-2 flex items-center justify-between">
                                            <span class="text-[11px] font-black text-primary" x-text="__('quiz.question') + ' ' + (qIndex + 1)"></span>
                                            <button type="button" @click="removeQuestion(qIndex)"
                                                    class="text-[11px] font-bold text-destructive hover:underline">
                                                {{ __('common.delete') }}
                                            </button>
                                        </div>

                                        <input type="hidden" :name="`questions[${qIndex}][correct]`" :value="question.correct">
                                        <input type="text" required minlength="3" maxlength="1000"
                                               x-model="question.text"
                                               :name="`questions[${qIndex}][text]`"
                                               :placeholder="__('quiz.questionPlaceholder')"
                                               class="w-full rounded-lg border border-input bg-card px-3 py-2 text-sm shadow-sm">

                                        <div class="mt-2 space-y-1.5">
                                            <template x-for="(option, oIndex) in question.options" :key="oIndex">
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="question.correct = oIndex"
                                                            class="flex size-5 shrink-0 items-center justify-center rounded-full border transition-colors"
                                                            :class="question.correct === oIndex
                                                                ? 'border-emerald-500 bg-emerald-500 text-white'
                                                                : 'border-border bg-card text-transparent'">
                                                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                                    </button>
                                                    <input type="text" required maxlength="500"
                                                           x-model="option.text"
                                                           :name="`questions[${qIndex}][options][${oIndex}][text]`"
                                                           :placeholder="__('quiz.optionPlaceholder')"
                                                           class="flex-1 rounded-lg border border-input bg-card px-3 py-1.5 text-xs shadow-sm">
                                                    <button type="button" @click="removeOption(qIndex, oIndex)"
                                                            class="text-muted-foreground hover:text-destructive" x-show="question.options.length > 2">
                                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <button type="button" @click="addOption(qIndex)"
                                                class="mt-2 text-[11px] font-bold text-primary hover:underline"
                                                x-show="question.options.length < 6">
                                            + {{ __('quiz.addOption') }}
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <button type="button" @click="addQuestion()"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-border py-2.5 text-xs font-bold text-muted-foreground transition-colors hover:border-primary/40 hover:text-primary">
                                <x-icon name="sparkles" class="size-3.5" />
                                {{ __('quiz.addQuestion') }}
                            </button>

                            @error('questions')<p class="text-xs font-semibold text-destructive">{{ $message }}</p>@enderror

                            <x-ui.button type="submit" variant="gold" class="w-full">
                                <x-icon name="award" class="size-4" />
                                <span>{{ __('quiz.createQuiz') }}</span>
                            </x-ui.button>
                        </form>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function quizMaker() {
            return {
                questions: [
                    { text: '', correct: 0, options: [{ text: '' }, { text: '' }] },
                ],
                addQuestion() {
                    this.questions.push({ text: '', correct: 0, options: [{ text: '' }, { text: '' }] });
                },
                removeQuestion(i) {
                    if (this.questions.length > 1) this.questions.splice(i, 1);
                },
                addOption(qi) {
                    if (this.questions[qi].options.length < 6) this.questions[qi].options.push({ text: '' });
                },
                removeOption(qi, oi) {
                    const q = this.questions[qi];
                    if (q.options.length > 2) {
                        q.options.splice(oi, 1);
                        if (q.correct >= q.options.length) q.correct = q.options.length - 1;
                        else if (q.correct > oi) q.correct = q.correct - 1;
                    }
                },
            };
        }
    </script>
@endpush
