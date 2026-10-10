@extends('layouts.workspace')

@section('title', __('builder.manage').' — '.$journey->title)

@section('workspace')
    @php
        $status = $journey->status?->value ?? 'DRAFT';
        $statusVariant = match ($status) {
            'PUBLISHED' => 'success',
            'PENDING_REVIEW' => 'gold',
            default => 'outline',
        };
        $totalLessons = $journey->modules->sum(fn ($m) => $m->lessons->count());
        $totalMinutes = $journey->modules->sum(fn ($m) => $m->lessons->sum('duration_min'));
    @endphp

    <x-layout.page-header
        :title="$journey->title"
        :subtitle="__('builder.manageSubtitle')">
        <x-slot:icon>
            <x-icon name="route" class="size-6" />
        </x-slot:icon>
        <x-slot:actions>
            @if ($status === 'DRAFT')
                <form method="POST" action="{{ route('instructor.journeys.submit', $journey->id) }}"
                      onsubmit="return confirm(__('builder.submitConfirm'))">
                    @csrf
                    <x-ui.button variant="gold" size="sm" type="submit">
                        <x-icon name="arrow" class="size-4" />
                        <span>{{ __('builder.submitReview') }}</span>
                    </x-ui.button>
                </form>
            @endif
            <x-ui.button variant="ghost" size="sm" tag="a" :href="route('instructor.journeys')">
                <x-icon name="arrow" class="size-4" />
                <span>{{ __('common.back') }}</span>
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

    {{-- ───────── Stats strip ───────── --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $strip = [
                ['label' => __('dash.journeys'), 'value' => $journey->modules->count(), 'icon' => 'package'],
                ['label' => __('common.lessons'), 'value' => $totalLessons, 'icon' => 'file'],
                ['label' => __('common.minutes'), 'value' => \App\Support\Format::duration($totalMinutes), 'icon' => 'calendar'],
                ['label' => __('common.students'), 'value' => $journey->studentsCount(), 'icon' => 'users'],
            ];
        @endphp
        @foreach ($strip as $i => $s)
            <div class="animate-rise" style="animation-delay: {{ number_format($i * 0.05, 2) }}s">
                <x-ui.card>
                    <x-ui.card-content class="flex items-center gap-3 p-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-secondary text-primary">
                            <x-icon :name="$s['icon']" class="size-4.5" />
                        </span>
                        <div>
                            <p class="text-lg font-extrabold leading-none">{{ $s['value'] }}</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">{{ $s['label'] }}</p>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        {{-- ───────── Curriculum manager ───────── --}}
        <div class="min-w-0 space-y-4" x-data="curriculum()">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold">{{ __('builder.curriculum') }}</h2>
                <x-ui.button variant="outline" size="sm" @click="addingModule = true" x-show="! addingModule">
                    <x-icon name="sparkles" class="size-3.5" />
                    <span>{{ __('builder.addSection') }}</span>
                </x-ui.button>
            </div>

            {{-- Add module form --}}
            <div x-show="addingModule" x-cloak class="rounded-2xl border border-primary/25 bg-primary/5 p-4">
                <form method="POST" action="{{ route('instructor.modules.store', $journey->id) }}">
                    @csrf
                    <div class="flex items-center gap-2">
                        <x-ui.input name="title" required minlength="2" maxlength="190"
                                    placeholder="{{ __('builder.sectionTitle') }}"
                                    x-ref="moduleInput" />
                        <x-ui.button type="submit" variant="gold" size="sm">{{ __('common.save') }}</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" @click="addingModule = false">{{ __('common.cancel') }}</x-ui.button>
                    </div>
                </form>
            </div>

            @if ($journey->modules->isEmpty())
                <x-ui.card>
                    <x-ui.card-content class="py-16 text-center">
                        <span class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-secondary text-primary">
                            <x-icon name="package" class="size-7" />
                        </span>
                        <p class="text-sm font-bold">{{ __('builder.noSections') }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">{{ __('builder.noSectionsHint') }}</p>
                    </x-ui.card-content>
                </x-ui.card>
            @else
                @foreach ($journey->modules as $moduleIndex => $module)
                    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm" x-data="{ open: {{ $moduleIndex === 0 ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-3 bg-secondary/40 px-4 py-3">
                            <button type="button" @click="open = ! open" class="flex min-w-0 flex-1 items-center gap-3 text-start">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-card text-xs font-black text-primary ring-1 ring-border ring-inset">
                                    {{ $moduleIndex + 1 }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-bold">{{ $module->title }}</span>
                                    <span class="block text-[11px] text-muted-foreground">
                                        {{ $module->lessons->count() }} {{ __('common.lessons') }}
                                        · {{ \App\Support\Format::duration($module->lessons->sum('duration_min')) }}
                                    </span>
                                </span>
                                <span class="text-muted-foreground transition-transform duration-200" ::class="open && 'rotate-90'">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                </span>
                            </button>
                            <form method="POST" action="{{ route('instructor.modules.destroy', $module->id) }}"
                                  onsubmit="return confirm(__('builder.deleteSectionConfirm'))">
                                @csrf
                                @method('DELETE')
                                <x-ui.button variant="ghost" size="icon-sm" type="submit" title="{{ __('common.delete') }}">
                                    <x-icon name="rotate" class="size-4 text-destructive" />
                                </x-ui.button>
                            </form>
                        </div>

                        <div class="divide-y divide-border/60" x-show="open" x-collapse>
                            @foreach ($module->lessons as $lesson)
                                <div class="px-4 py-3" x-data="{ edit: false }">
                                    <div class="flex items-center gap-3" x-show="! edit">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-secondary text-primary">
                                            <x-icon name="file" class="size-4" />
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="flex items-center gap-2 truncate text-sm font-semibold">
                                                {{ $lesson->title }}
                                                @if ($lesson->is_preview)
                                                    <x-ui.badge variant="gold" class="text-[9px]">{{ __('builder.preview') }}</x-ui.badge>
                                                @endif
                                            </p>
                                            <p class="truncate text-[11px] text-muted-foreground">
                                                {{ $lesson->duration_min > 0 ? \App\Support\Format::duration($lesson->duration_min) : '—' }}
                                                {{ $lesson->video_url ? ' · 🎬' : '' }}
                                                {{ $lesson->resources->count() ? ' · 📎 ' . $lesson->resources->count() : '' }}
                                            </p>
                                        </div>
                                        <button type="button" @click="edit = true"
                                                class="text-xs font-bold text-primary hover:underline">{{ __('common.edit') }}</button>
                                        <form method="POST" action="{{ route('instructor.lessons.destroy', $lesson->id) }}"
                                              onsubmit="return confirm(__('common.confirmDelete'))">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="ghost" size="icon-sm" type="submit" title="{{ __('common.delete') }}">
                                                <x-icon name="rotate" class="size-4 text-destructive" />
                                            </x-ui.button>
                                        </form>
                                    </div>

                                    {{-- Edit lesson form --}}
                                    <form method="POST" action="{{ route('instructor.lessons.update', $lesson->id) }}"
                                          class="grid gap-3" x-show="edit" x-cloak>
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.input name="title" required minlength="2" maxlength="190" value="{{ $lesson->title }}" />
                                        <textarea name="description" rows="2" maxlength="3000"
                                                  class="w-full rounded-xl border border-input bg-card px-4 py-2.5 text-sm shadow-sm"
                                                  placeholder="{{ __('builder.lessonDescription') }}">{{ $lesson->description }}</textarea>
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <x-ui.input name="video_url" type="url" maxlength="500" value="{{ $lesson->video_url }}"
                                                        placeholder="Video URL" />
                                            <x-ui.input name="duration_min" type="number" min="0" max="600" value="{{ $lesson->duration_min }}"
                                                        placeholder="{{ __('common.minutes') }}" />
                                            <label class="flex h-11 items-center gap-2 rounded-xl border border-input bg-card px-4 text-sm">
                                                <input type="checkbox" name="is_preview" value="1" @checked($lesson->is_preview)
                                                       class="size-4 accent-indigo-600">
                                                <span>{{ __('builder.preview') }}</span>
                                            </label>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <x-ui.button variant="ghost" size="sm" @click="edit = false">{{ __('common.cancel') }}</x-ui.button>
                                            <x-ui.button type="submit" size="sm" variant="gold">{{ __('common.save') }}</x-ui.button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach

                            {{-- Add lesson form --}}
                            <div class="px-4 py-3" x-data="{ open: false }">
                                <button type="button" @click="open = ! open" x-show="! open"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-border py-2.5 text-xs font-bold text-muted-foreground transition-colors hover:border-primary/40 hover:text-primary">
                                    <x-icon name="sparkles" class="size-3.5" />
                                    {{ __('builder.addLesson') }}
                                </button>

                                <form method="POST" action="{{ route('instructor.lessons.store', $module->id) }}"
                                      class="grid gap-3" x-show="open" x-cloak>
                                    @csrf
                                    <x-ui.input name="title" required minlength="2" maxlength="190"
                                                placeholder="{{ __('builder.lessonTitle') }}" />
                                    <textarea name="description" rows="2" maxlength="3000"
                                              class="w-full rounded-xl border border-input bg-card px-4 py-2.5 text-sm shadow-sm"
                                              placeholder="{{ __('builder.lessonDescription') }}"></textarea>
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <x-ui.input name="video_url" type="url" maxlength="500"
                                                    placeholder="Video URL" />
                                        <x-ui.input name="duration_min" type="number" min="0" max="600" value="0"
                                                    placeholder="{{ __('common.minutes') }}" />
                                        <label class="flex h-11 items-center gap-2 rounded-xl border border-input bg-card px-4 text-sm">
                                            <input type="checkbox" name="is_preview" value="1" class="size-4 accent-indigo-600">
                                            <span>{{ __('builder.preview') }}</span>
                                        </label>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <x-ui.button variant="ghost" size="sm" @click="open = false">{{ __('common.cancel') }}</x-ui.button>
                                        <x-ui.button type="submit" size="sm" variant="gold">{{ __('builder.addLesson') }}</x-ui.button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- ───────── Journey settings ───────── --}}
        <aside class="min-w-0">
            <div class="lg:sticky lg:top-24 space-y-4">
                <x-ui.card>
                    <x-ui.card-content class="p-5">
                        <h3 class="mb-4 text-sm font-bold">{{ __('builder.settings') }}</h3>

                        <form method="POST" action="{{ route('instructor.journeys.update', $journey->id) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-muted-foreground">{{ __('builder.title') }}</label>
                                <x-ui.input name="title" required minlength="3" maxlength="190" value="{{ $journey->title }}" />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-muted-foreground">{{ __('builder.tagline') }}</label>
                                <x-ui.input name="tagline" maxlength="255" value="{{ $journey->tagline }}" />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-muted-foreground">{{ __('common.level') }}</label>
                                    <select name="level" class="h-11 w-full rounded-xl border border-input bg-card px-3 text-sm shadow-sm">
                                        @foreach (['BEGINNER', 'INTERMEDIATE', 'ADVANCED', 'MASTER'] as $lvl)
                                            <option value="{{ $lvl }}" @selected($journey->level?->value === $lvl)>{{ __('levels.'.$lvl) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-muted-foreground">{{ __('builder.language') }}</label>
                                    <select name="language" class="h-11 w-full rounded-xl border border-input bg-card px-3 text-sm shadow-sm">
                                        <option value="ar" @selected($journey->language === 'ar')>العربية</option>
                                        <option value="en" @selected($journey->language === 'en')>English</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-muted-foreground">{{ __('builder.pricing') }}</label>
                                    <select name="is_free" class="h-11 w-full rounded-xl border border-input bg-card px-3 text-sm shadow-sm"
                                            onchange="document.getElementById('editPrice').classList.toggle('hidden', this.value === '1')">
                                        <option value="1" @selected($journey->is_free)>{{ __('builder.free') }}</option>
                                        <option value="0" @selected(! $journey->is_free)>{{ __('builder.paid') }}</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5 {{ $journey->is_free ? 'hidden' : '' }}" id="editPrice">
                                    <label class="text-xs font-bold text-muted-foreground">{{ __('builder.price') }} ($)</label>
                                    <x-ui.input name="price" type="number" min="0" max="9999" value="{{ $journey->price }}" />
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-muted-foreground">{{ __('builder.coverUrl') }}</label>
                                <x-ui.input name="cover_image" type="url" maxlength="500" value="{{ $journey->cover_image }}" />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-muted-foreground">{{ __('builder.description') }}</label>
                                <textarea name="description" rows="4" maxlength="5000"
                                          class="w-full rounded-xl border border-input bg-card px-4 py-3 text-sm shadow-sm">{{ $journey->description }}</textarea>
                            </div>

                            <x-ui.button type="submit" variant="gold" class="w-full">{{ __('common.save') }}</x-ui.button>
                        </form>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-content class="p-5">
                        <h3 class="mb-3 text-sm font-bold">{{ __('builder.nextSteps') }}</h3>
                        @php
                            $steps = [
                                ['href' => route('instructor.bag.create'), 'label' => __('dash.bagTitle'), 'icon' => 'package'],
                                ['href' => route('instructor.quizzes'), 'label' => __('quiz.title'), 'icon' => 'award'],
                                ['href' => route('instructor.council'), 'label' => __('dash.councilTitle'), 'icon' => 'messages'],
                            ];
                        @endphp
                        <div class="space-y-2">
                            @foreach ($steps as $step)
                                <a href="{{ $step['href'] }}"
                                   class="flex items-center gap-3 rounded-xl border border-border/70 bg-secondary/30 px-3 py-2.5 text-sm font-semibold transition-colors hover:border-primary/30 hover:bg-secondary">
                                    <span class="flex size-8 items-center justify-center rounded-lg bg-card text-primary ring-1 ring-border ring-inset">
                                        <x-icon :name="$step['icon']" class="size-4" />
                                    </span>
                                    <span class="flex-1">{{ $step['label'] }}</span>
                                    <x-icon name="arrow" class="size-3.5 text-muted-foreground" />
                                </a>
                            @endforeach
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </aside>
    </div>
@endsection
