@extends('layouts.workspace')

@section('title', __('dash.bagTitle').' — '.__('dash.upload'))

@section('workspace')
    <x-layout.page-header
        :title="__('builder.uploadAsset')"
        :subtitle="__('builder.uploadAssetSubtitle')">
        <x-slot:icon>
            <x-icon name="package" class="size-6" />
        </x-slot:icon>
        <x-slot:actions>
            <x-ui.button variant="ghost" size="sm" tag="a" :href="route('instructor.bag')">
                <x-icon name="arrow" class="size-4" />
                <span>{{ __('common.back') }}</span>
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.card class="mx-auto max-w-2xl">
        <x-ui.card-content class="p-6 sm:p-8">
            <form method="POST" action="{{ route('instructor.bag.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-muted-foreground">{{ __('builder.journey') }}</label>
                    <select id="journeySelect"
                            class="h-11 w-full rounded-xl border border-input bg-card px-4 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                        <option value="">— {{ __('quiz.selectJourney') }} —</option>
                        @foreach ($journeys as $j)
                            <option value="{{ $j->id }}">{{ $j->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.section') }}</label>
                        <select id="moduleSelect"
                                class="h-11 w-full rounded-xl border border-input bg-card px-4 text-sm shadow-sm">
                            <option value="">— {{ __('builder.addSection') }} —</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.lesson') }} *</label>
                        <select name="lesson_id" id="lessonSelect" required
                                class="h-11 w-full rounded-xl border border-input bg-card px-4 text-sm shadow-sm">
                            <option value="">— {{ __('builder.lesson') }} —</option>
                        </select>
                        @error('lesson_id')<p class="text-xs font-semibold text-destructive">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-muted-foreground">{{ __('builder.file') }} *</label>
                    <input type="file" name="file" required
                           class="w-full rounded-xl border border-input bg-card px-4 py-3 text-sm shadow-sm file:me-3 file:rounded-lg file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-xs file:font-bold" />
                    <p class="text-[11px] text-muted-foreground">
                        {{ __('builder.fileHint') }}
                    </p>
                    @error('file')<p class="text-xs font-semibold text-destructive">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-muted-foreground">{{ __('builder.customName') }}</label>
                    <x-ui.input name="name" maxlength="190" placeholder="{{ __('builder.customNamePlaceholder') }}" />
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-border pt-5">
                    <x-ui.button variant="ghost" tag="a" :href="route('instructor.bag')">{{ __('common.cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="gold">
                        <x-icon name="sparkles" class="size-4" />
                        <span>{{ __('dash.upload') }}</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>

    @push('scripts')
        <script>
            (function () {
                const modules = @json($modules);
                const lessons = @json($lessons);
                const journeyEl = document.getElementById('journeySelect');
                const moduleEl = document.getElementById('moduleSelect');
                const lessonEl = document.getElementById('lessonSelect');

                const emptyModule = {{ Js::from(__('builder.selectSection')) }};
                const emptyLesson = {{ Js::from(__('builder.selectLesson')) }};

                journeyEl.addEventListener('change', function () {
                    moduleEl.innerHTML = '<option value="">' + emptyModule + '</option>';
                    lessonEl.innerHTML = '<option value="">' + emptyLesson + '</option>';
                    modules
                        .filter(m => String(m.journey_id) === this.value)
                        .forEach(m => {
                            moduleEl.innerHTML += '<option value="' + m.id + '">' + m.title + '</option>';
                        });
                });

                moduleEl.addEventListener('change', function () {
                    lessonEl.innerHTML = '<option value="">' + emptyLesson + '</option>';
                    lessons
                        .filter(l => String(l.module_id) === this.value)
                        .forEach(l => {
                            lessonEl.innerHTML += '<option value="' + l.id + '">' + l.title + '</option>';
                        });
                });
            })();
        </script>
    @endpush
@endsection
