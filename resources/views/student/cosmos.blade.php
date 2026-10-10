@extends('layouts.workspace')

@section('title', __('cosmos.title'))

@section('workspace')
    @php
        $typeLabels = [
            'ARTICLE' => __('cosmos.articles'),
            'BOOK' => __('cosmos.books'),
            'SUMMARY' => __('cosmos.summaries'),
        ];

        $tabLabels = ['ALL' => __('common.viewAll')] + $typeLabels;

        $tabIcons = [
            'ALL' => 'telescope',
            'ARTICLE' => 'file',
            'BOOK' => 'book',
            'SUMMARY' => 'bookmark',
        ];
    @endphp

    <x-layout.page-header :title="__('cosmos.title')" :subtitle="__('cosmos.subtitle')">
        <x-slot:icon>
            <x-icon name="telescope" class="size-6" />
        </x-slot:icon>

        <x-slot:actions>
            <a href="{{ route('explore') }}">
                <x-ui.badge variant="gold" class="cursor-pointer gap-1.5 px-3 py-1.5">
                    {{ __('paths.browse') }}
                </x-ui.badge>
            </a>
        </x-slot:actions>
    </x-layout.page-header>

    <div x-data="{ tab: '{{ $tabs[0] }}' }">
        <div class="mb-6 flex flex-wrap gap-2">
            @foreach ($tabs as $tab)
                <button type="button"
                        @click="tab = @js($tab)"
                        :class="tab === @js($tab)
                            ? 'text-primary-foreground'
                            : 'text-muted-foreground hover:bg-secondary hover:text-foreground'"
                        class="relative flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition-colors">
                    <span class="absolute inset-0 rounded-full bg-primary shadow-lg shadow-primary/25 transition-transform duration-300"
                          x-show="tab === @js($tab)"
                          x-cloak></span>

                    <x-icon :name="$tabIcons[$tab]" class="relative z-10 size-4" />
                    <span class="relative z-10">{{ $tabLabels[$tab] }}</span>
                </button>
            @endforeach
        </div>

        @if ($items->isEmpty())
            <x-ui.card>
                <x-ui.card-content class="flex flex-col items-center gap-3 py-16 text-center">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-secondary text-muted-foreground">
                        <x-icon name="telescope" class="size-6" />
                    </span>

                    <p class="text-sm text-muted-foreground">{{ __('common.empty') }}</p>
                </x-ui.card-content>
            </x-ui.card>
        @else
            <div class="stagger grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $item)
                    <div x-show="tab === 'ALL' || tab === @js($item->type->value)" x-cloak>
                        <x-ui.card class="group h-full overflow-hidden transition-all hover:border-primary/25 hover:shadow-lg hover:shadow-primary/10">
                            <div class="relative aspect-16/9 overflow-hidden bg-linear-to-br from-indigo-950 via-indigo-900 to-slate-900">
                                @if ($item->cover_url)
                                    <img src="{{ $item->cover_url }}" alt="{{ $item->title }}"
                                         class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <x-icon name="file" class="size-8 text-white/30" />
                                    </div>
                                @endif

                                <x-ui.badge variant="gold" class="absolute top-3 start-3">
                                    {{ $typeLabels[$item->type->value] }}
                                </x-ui.badge>
                            </div>

                            <x-ui.card-content class="p-5">
                                <h3 class="line-clamp-2 font-bold leading-snug">{{ $item->title }}</h3>

                                @if ($item->excerpt)
                                    <p class="mt-2 line-clamp-2 text-sm text-muted-foreground">{{ $item->excerpt }}</p>
                                @endif

                                <div class="mt-4 flex items-center justify-between gap-3 text-xs text-muted-foreground">
                                    <span class="truncate font-semibold">{{ $item->author?->display_name ?? '—' }}</span>
                                    <span class="shrink-0">{{ $item->read_min }} {{ __('cosmos.readMin') }}</span>
                                </div>
                            </x-ui.card-content>
                        </x-ui.card>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
