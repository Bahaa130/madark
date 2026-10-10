@extends('layouts.workspace')

@section('title', __('dash.bagTitle'))

@section('workspace')
    <x-layout.page-header
        :title="__('dash.bagTitle')"
        :subtitle="__('builder.bagTreeSubtitle')">
        <x-slot:icon>
            <x-icon name="package" class="size-6" />
        </x-slot:icon>
        <x-slot:actions>
            <x-ui.button variant="gold" size="sm" tag="a" :href="route('instructor.bag.create')">
                <x-icon name="sparkles" class="size-4" />
                <span>{{ __('dash.upload') }}</span>
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if (count($assets) === 0)
        <x-ui.card>
            <x-ui.card-content class="flex flex-col items-center gap-4 py-20 text-center">
                <span class="flex size-16 items-center justify-center rounded-2xl bg-secondary text-primary">
                    <x-icon name="package" class="size-8" />
                </span>
                <p class="text-sm text-muted-foreground">{{ __('common.empty') }}</p>
                <x-ui.button variant="gold" tag="a" :href="route('instructor.bag.create')">
                    <x-icon name="sparkles" class="size-4" />
                    <span>{{ __('dash.upload') }}</span>
                </x-ui.button>
            </x-ui.card-content>
        </x-ui.card>
    @else
        @php
            $tree = [];
            foreach ($assets as $a) {
                $j = $a['lesson']['module']['journey']['title'] ?? '—';
                $m = $a['lesson']['module']['title'] ?? '—';
                $l = $a['lesson']['title'] ?? '—';
                $tree[$j][$m][$l][] = $a;
            }
            $typeIcon = fn ($ext) => match ($ext) {
                'pdf', 'doc', 'docx' => 'file',
                'mp4', 'webm' => 'file',
                'mp3', 'wav' => 'file',
                'jpg', 'jpeg', 'png', 'webp', 'gif' => 'file',
                default => 'file',
            };
        @endphp

        <div class="space-y-4">
            @foreach ($tree as $journeyTitle => $modules)
                <x-ui.card>
                    <x-ui.card-content class="p-5">
                        <h2 class="mb-4 flex items-center gap-2 text-sm font-extrabold">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-secondary text-primary">
                                <x-icon name="route" class="size-3.5" />
                            </span>
                            {{ $journeyTitle }}
                        </h2>

                        <div class="space-y-4">
                            @foreach ($modules as $moduleTitle => $lessons)
                                <div>
                                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-muted-foreground">
                                        {{ $moduleTitle }}
                                    </h3>
                                    <div class="space-y-3">
                                        @foreach ($lessons as $lessonTitle => $items)
                                            <div class="rounded-2xl border border-border/70 bg-secondary/20 p-3">
                                                <p class="mb-2 text-sm font-semibold">{{ $lessonTitle }}</p>
                                                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                                    @foreach ($items as $a)
                                                        @php
                                                            $size = (int) ($a['sizeKb'] ?? 0);
                                                            $sizeStr = $size > 1024
                                                                ? number_format($size / 1024, 1).' MB'
                                                                : $size.' KB';
                                                            $ext = $a['extension'] ?: strtolower(pathinfo($a['name'], PATHINFO_EXTENSION));
                                                        @endphp
                                                        <div class="group flex items-center gap-3 rounded-xl border border-border bg-card/80 px-3 py-2 shadow-sm transition-all hover:border-primary/25 hover:shadow-md">
                                                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-secondary text-primary">
                                                                <x-icon :name="$typeIcon($ext)" class="size-4" />
                                                            </span>
                                                            <div class="min-w-0 flex-1">
                                                                <p class="truncate text-xs font-semibold">{{ $a['name'] }}</p>
                                                                <p class="text-[10px] text-muted-foreground">
                                                                    {{ $sizeStr }} @if($ext) • .{{ $ext }} @endif
                                                                </p>
                                                            </div>
                                                            <div class="flex items-center gap-0.5">
                                                                <x-ui.button variant="ghost" size="icon-sm" tag="a" :href="$a['url']"
                                                                             target="_blank" rel="noreferrer" title="{{ __('common.preview') ?? 'Preview' }}">
                                                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                                                </x-ui.button>
                                                                <x-ui.button variant="ghost" size="icon-sm" tag="a" :href="$a['url']" download
                                                                             title="{{ __('common.download') }}">
                                                                    <x-icon name="rotate" class="size-4 hidden" />
                                                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                                                                </x-ui.button>
                                                                <form method="POST" action="{{ route('instructor.bag.destroy', $a['id']) }}"
                                                                      onsubmit="return confirm(__('common.confirmDelete'))">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <x-ui.button variant="ghost" size="icon-sm" type="submit" title="{{ __('common.delete') ?? 'Delete' }}">
                                                                        <svg class="size-4 text-destructive" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                                    </x-ui.button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            @endforeach
        </div>
    @endif
@endsection
