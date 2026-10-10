@props([
    'journey',
    'completedLessonIds' => [],
    'isEnrolled' => false,
])

@php
    use App\Support\Format;

    $modules = $journey->modules;
    $flatLessons = $modules->flatMap(fn ($m) => $m->lessons);
    $completedIds = collect($completedLessonIds)->map(fn ($id) => (string) $id);
@endphp

{{--
    Alpine owns the player state that framer-motion + useState owned in the
    reference: the active lesson, the completion set, and open modules.
--}}
<div class="mx-auto max-w-7xl"
     x-data="{
        lessons: {{ \Illuminate\Support\Js::from($flatLessons->map(fn ($l) => [
            'id' => (string) $l->id,
            'title' => $l->title,
            'description' => $l->description,
            'videoUrl' => $l->video_url,
            'durationMin' => (int) $l->duration_min,
            'resources' => $l->relationLoaded('resources')
                ? $l->resources->map(fn ($a) => [
                    'id' => (string) $a->id,
                    'name' => $a->name,
                    'url' => $a->url,
                    'sizeKb' => (int) $a->size_kb,
                ])->all()
                : [],
        ])->values()) }},
        modules: {{ \Illuminate\Support\Js::from($modules->map(fn ($m) => [
            'id' => (string) $m->id,
            'lessons' => $m->lessons->pluck('id')->map(fn ($id) => (string) $id)->values(),
        ])->values()) }},
        done: {{ \Illuminate\Support\Js::from($completedIds) }},
        activeId: {{ \Illuminate\Support\Js::from((string) ($flatLessons->first()->id ?? '')) }},
        enrolled: {{ $isEnrolled ? 'true' : 'false' }},
        saving: false,
        joining: false,
        openModules: {{ \Illuminate\Support\Js::from($modules->take(1)->pluck('id')->map(fn ($id) => (string) $id)) }},

        get activeIndex() { return this.lessons.findIndex(l => l.id === this.activeId); },
        get active() { return this.lessons[this.activeIndex] ?? this.lessons[0] ?? null; },
        get prev() { return this.activeIndex > 0 ? this.lessons[this.activeIndex - 1] : null; },
        get next() { return this.lessons[this.activeIndex + 1] ?? null; },
        get count() { return this.lessons.length; },
        get progress() { return this.count ? (this.done.length / this.count) * 100 : 0; },

        isDone(id) { return this.done.includes(String(id)); },

        select(id) {
            this.activeId = String(id);
            const owner = this.modules.find(m => m.lessons.includes(String(id)));
            if (owner && !this.openModules.includes(owner.id)) this.openModules.push(owner.id);
        },

        toggleModule(id) {
            const i = this.openModules.indexOf(String(id));
            i === -1 ? this.openModules.push(String(id)) : this.openModules.splice(i, 1);
        },

        doneIn(module) {
            return module.lessons.filter(id => this.isDone(id)).length;
        },

        async toggleComplete() {
            const lesson = this.active;
            if (!lesson || this.saving) return;

            const wasDone = this.isDone(lesson.id);
            this.saving = true;

            // Optimistic, matching the reference.
            wasDone
                ? this.done = this.done.filter(id => id !== lesson.id)
                : this.done.push(lesson.id);

            try {
                const response = await fetch('{{ route('api.progress') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ lessonId: Number(lesson.id), completed: !wasDone }),
                });

                if (!response.ok) throw new Error('save failed');

                const data = await response.json();

                if (data.certificateSerial) {
                    window.location.href = '{{ route('student.profile') }}';
                }
            } catch (e) {
                // Roll back the optimistic update.
                wasDone ? this.done.push(lesson.id) : this.done = this.done.filter(id => id !== lesson.id);
            } finally {
                this.saving = false;
            }
        },

        async join() {
            if (this.joining) return;
            this.joining = true;

            try {
                const response = await fetch('{{ route('api.enroll') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ journeyId: {{ $journey->id }} }),
                });

                if (response.ok) this.enrolled = true;
            } finally {
                this.joining = false;
            }
        },
     }">

    {{-- ───────── Journey header ───────── --}}
    <div class="mb-6">
        <a href="{{ route('student.paths') }}"
           class="mb-4 inline-flex items-center gap-1.5 text-xs font-bold text-muted-foreground transition-colors hover:text-primary">
            @if ($dir === 'rtl')
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
            @else
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
            @endif
            {{ __('paths.title') }}
        </a>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <x-ui.badge variant="gold">{{ Format::level($journey->level) }}</x-ui.badge>

                    <x-ui.badge variant="secondary" class="gap-1">
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                        </svg>
                        {{ $journey->studentsCount() }} {{ __('common.students') }}
                    </x-ui.badge>

                    <x-ui.badge variant="secondary" class="gap-1">
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8v8a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 16V8a2 2 0 0 1 1-1.73l7-4a2 2 0 0 1 2 0l7 4A2 2 0 0 1 21 8z" />
                        </svg>
                        {{ count($flatLessons) }} {{ __('common.lessons') }}
                    </x-ui.badge>
                </div>

                <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $journey->title }}</h1>

                @if ($journey->tagline)
                    <p class="mt-1.5 text-sm text-muted-foreground">{{ $journey->tagline }}</p>
                @endif
            </div>

            <div class="w-full max-w-xs shrink-0">
                <div class="mb-2 flex items-center justify-between text-xs font-bold">
                    <span class="text-muted-foreground">{{ __('common.progress') }}</span>
                    <span class="text-primary" x-text="Math.round(progress) + '%'">{{ round($journey->progress_percent) }}%</span>
                </div>

                <div class="h-2 w-full overflow-hidden rounded-full bg-secondary">
                    <div class="h-full rounded-full bg-linear-to-l from-gold to-gold-soft transition-all duration-700 ease-out"
                         :style="`width: ${progress}%`"
                         style="width: {{ $journey->progress_percent }}%"></div>
                </div>

                <p class="mt-1.5 text-[11px] text-muted-foreground">
                    <span x-text="done.length">{{ count($completedIds) }}</span> / {{ count($flatLessons) }} {{ __('common.completed') }}
                </p>
            </div>
        </div>
    </div>

    {{-- ───────── Enroll prompt ───────── --}}
    <x-ui.card class="mb-6 border-gold/25 bg-linear-to-l from-gold/8 to-transparent" x-show="! enrolled" x-cloak>
        <x-ui.card-content class="flex flex-wrap items-center justify-between gap-4 p-5">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gold/15 text-gold ring-1 ring-gold/20 ring-inset">
                    <svg class="size-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l1.9 5.6L19.5 9.5 13.9 11.4 12 17l-1.9-5.6L4.5 9.5l5.6-1.9L12 2z" />
                    </svg>
                </span>

                <div>
                    <p class="text-sm font-bold">{{ __('common.enroll') }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ $dir === 'rtl'
                            ? 'انضم إلى الرحلة لتتبّع تقدمك واحفظ إنجازاتك'
                            : 'Join to track your progress and save achievements' }}
                    </p>
                </div>
            </div>

            <x-ui.button variant="gold" size="sm" @click="join()" ::disabled="joining">
                <span x-show="! joining">{{ __('common.enroll') }}</span>
            </x-ui.button>
        </x-ui.card-content>
    </x-ui.card>

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        {{-- ───────── Main stage ───────── --}}
        <div class="min-w-0 space-y-5">
            <x-ui.card class="overflow-hidden">
                <div class="relative aspect-video bg-black">
                    <template x-if="active && active.videoUrl">
                        {{-- :src keeps the element keyed to the lesson so the
                             browser reloads the source on lesson change. --}}
                        <video :src="active.videoUrl" :poster="{{ $journey->cover_image }}"
                               controls playsinline class="size-full"></video>
                    </template>

                    <div x-show="! active || ! active.videoUrl"
                         class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-white/40">
                        <svg class="size-16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M10 8.5l6 3.5-6 3.5z" />
                        </svg>
                        <p class="text-sm font-semibold">
                            {{ $dir === 'rtl' ? 'لا يوجد فيديو لهذا الدرس' : 'No video for this lesson' }}
                        </p>
                    </div>
                </div>

                <x-ui.card-content class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold" x-text="active ? active.title : ''">{{ $flatLessons->first()?->title }}</h2>

                            <p class="mt-1 text-xs text-muted-foreground" x-show="active && active.durationMin > 0">
                                <span x-text="active.durationMin"></span>
                            </p>
                        </div>

                        <x-ui.button size="sm" @click="toggleComplete()"
                                    ::class="isDone(active?.id) ? 'bg-linear-to-l from-[#b8860b] via-[#d4af37] to-[#e8cf7a] text-[#1c1503]' : ''"
                                    ::disabled="saving">
                            <span x-text="isDone(active?.id)
                                ? @js(__('common.completed'))
                                : @js(__('common.markComplete'))">{{ __('common.markComplete') }}</span>
                        </x-ui.button>
                    </div>

                    <p class="mt-4 text-sm leading-relaxed text-muted-foreground" x-show="active && active.description"
                       x-text="active ? active.description : ''">{{ $flatLessons->first()?->description }}</p>

                    {{-- Resources --}}
                    <div class="mt-6" x-show="active && active.resources.length > 0">
                        <h3 class="mb-3 flex items-center gap-2 text-sm font-bold">
                            <svg class="size-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" />
                            </svg>
                            {{ __('common.resources') }}
                        </h3>

                        <ul class="grid gap-2 sm:grid-cols-2">
                            <template x-for="resource in (active ? active.resources : [])" :key="resource.id">
                                <li>
                                    <a :href="resource.url" download target="_blank" rel="noreferrer"
                                       class="group flex items-center gap-3 rounded-xl border border-border bg-secondary/40 px-3 py-2.5 transition-all hover:border-primary/30 hover:bg-secondary">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-card text-primary ring-1 ring-border ring-inset">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                <path d="M14 2v6h6" />
                                            </svg>
                                        </span>

                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-xs font-bold" x-text="resource.name"></span>
                                            <span class="block text-[10px] text-muted-foreground"
                                                  x-text="resource.sizeKb > 1024
                                                      ? (resource.sizeKb / 1024).toFixed(1) + ' MB'
                                                      : resource.sizeKb + ' KB'"></span>
                                        </span>

                                        <svg class="size-4 shrink-0 text-muted-foreground transition-colors group-hover:text-primary"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" />
                                        </svg>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Lesson navigation --}}
                    <div class="mt-7 flex items-center justify-between gap-3 border-t border-border pt-5">
                        <x-ui.button variant="ghost" size="sm" @click="prev && select(prev.id)" ::disabled="! prev">
                            @if ($dir === 'rtl')
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                            @else
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                            @endif
                            {{ __('common.back') }}
                        </x-ui.button>

                        <span class="text-xs font-bold text-muted-foreground">
                            <span x-text="activeIndex + 1"></span> / {{ count($flatLessons) }}
                        </span>

                        <x-ui.button variant="outline" size="sm" @click="next && select(next.id)" ::disabled="! next">
                            {{ __('common.continue') }}
                            @if ($dir === 'rtl')
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                            @else
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                            @endif
                        </x-ui.button>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            {{-- Instructor + about --}}
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.card>
                    <x-ui.card-content class="p-5">
                        <h3 class="mb-4 text-sm font-bold">{{ __('journey.instructor') }}</h3>

                        <div class="flex items-center gap-3">
                            @if ($journey->instructor?->image)
                                <img src="{{ $journey->instructor->image }}" alt="{{ $journey->instructor->name }}"
                                     class="size-10 rounded-full object-cover">
                            @else
                                <span class="flex size-10 items-center justify-center rounded-full bg-linear-to-br from-primary to-indigo-900 text-sm font-bold text-primary-foreground">
                                    {{ mb_strtoupper(mb_substr((string) $journey->instructor?->name, 0, 1) ?: '?') }}
                                </span>
                            @endif

                            <div class="min-w-0">
                                <p class="flex items-center gap-1 truncate text-sm font-bold">
                                    {{ $journey->instructor?->name ?? '—' }}
                                    <svg class="size-3.5 shrink-0 text-primary" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        <path d="M9 12l2 2 4-4" />
                                    </svg>
                                </p>

                                @if ($journey->instructor?->headline)
                                    <p class="truncate text-xs text-muted-foreground">{{ $journey->instructor->headline }}</p>
                                @endif
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-content class="p-5">
                        <h3 class="mb-2 text-sm font-bold">{{ __('journey.about') }}</h3>
                        <p class="line-clamp-4 text-sm leading-relaxed text-muted-foreground">
                            {{ $journey->description ?: $journey->tagline ?: '—' }}
                        </p>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>

        {{-- ───────── Timeline ───────── --}}
        <aside class="min-w-0">
            <div class="lg:sticky lg:top-24">
                <x-ui.card class="max-h-[calc(100dvh-8rem)] overflow-hidden">
                    <div class="border-b border-border bg-card/60 px-5 py-4">
                        <h2 class="flex items-center gap-2 font-bold">
                            <svg class="size-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 8v8a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 16V8a2 2 0 0 1 1-1.73l7-4a2 2 0 0 1 2 0l7 4A2 2 0 0 1 21 8z" />
                            </svg>
                            {{ __('journey.timeline') }}
                        </h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">{{ __('journey.curriculum') }}</p>
                    </div>

                    <div class="max-h-[calc(100dvh-13rem)] overflow-y-auto">
                        @foreach ($modules as $moduleIndex => $module)
                            @php
                                $moduleId = (string) $module->id;
                                $doneInModule = $module->lessons->filter(
                                    fn ($l) => $completedIds->contains((string) $l->id)
                                )->count();
                            @endphp

                            <div class="border-b border-border/60 last:border-0">
                                <button type="button" @click="toggleModule(@js($moduleId))"
                                        class="flex w-full items-center gap-3 px-5 py-3.5 text-start transition-colors hover:bg-secondary/40">
                                    <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-secondary text-[11px] font-black text-muted-foreground">
                                        {{ $moduleIndex + 1 }}
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-xs font-bold">{{ $module->title }}</span>
                                        <span class="block text-[10px] text-muted-foreground">
                                            <span x-text="doneIn(modules[{{ $moduleIndex }}])">{{ $doneInModule }}</span>/{{ $module->lessons->count() }} {{ __('common.completed') }}
                                        </span>
                                    </span>

                                    <span class="text-muted-foreground transition-transform duration-200"
                                          ::class="openModules.includes(@js($moduleId)) && 'rotate-90'">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 18l6-6-6-6" />
                                        </svg>
                                    </span>
                                </button>

                                {{-- x-collapse replaces the framer-motion height animation. --}}
                                <ul class="overflow-hidden" x-show="openModules.includes(@js($moduleId))" x-collapse>
                                    @foreach ($module->lessons as $lesson)
                                        @php $lessonId = (string) $lesson->id; @endphp

                                        <li>
                                            <button type="button" @click="select(@js($lessonId))"
                                                    @class([
                                                        'relative flex w-full items-center gap-2.5 py-2.5 pe-5 ps-12 text-start text-xs transition-colors',
                                                        'text-primary' => $completedIds->contains($lessonId),
                                                        'text-muted-foreground hover:text-foreground' => ! $completedIds->contains($lessonId),
                                                    ])
                                                    :class="activeId === @js($lessonId) ? 'text-primary font-bold' : ''">
                                                <span class="absolute inset-y-1 start-0 w-1 rounded-full bg-gold"
                                                      x-show="activeId === @js($lessonId)"></span>

                                                <svg class="size-4 shrink-0"
                                                     :class="isDone(@js($lessonId)) ? 'text-emerald-500' : 'opacity-40'"
                                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    @if ($completedIds->contains($lessonId))
                                                        <circle cx="12" cy="12" r="10" />
                                                        <path d="M8 12l3 3 5-6" />
                                                    @else
                                                        <circle cx="12" cy="12" r="9" />
                                                    @endif
                                                </svg>

                                                <span class="min-w-0 flex-1 truncate">{{ $lesson->title }}</span>

                                                @if ($lesson->duration_min > 0)
                                                    <span class="shrink-0 text-[10px] opacity-70">{{ $lesson->duration_min }}</span>
                                                @endif
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            </div>
        </aside>
    </div>
</div>