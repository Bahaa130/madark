@extends('layouts.workspace')

@section('title', __('profile.title'))

@section('workspace')
    @php
        use App\Support\Format;
    @endphp

    <x-layout.page-header :title="__('profile.title')" :subtitle="__('profile.subtitle')">
        <x-slot:icon>
            <x-icon name="user" class="size-6" />
        </x-slot:icon>

        <x-slot:actions>
            <a href="{{ route('student.paths') }}">
                <x-ui.badge variant="gold" class="cursor-pointer gap-1.5 px-3 py-1.5">
                    {{ __('nav.student.paths') }}
                </x-ui.badge>
            </a>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="animate-rise">
        <x-ui.card class="overflow-hidden">
            <div class="h-24 bg-linear-to-l from-indigo-950 via-primary/30 to-gold/20"></div>

            <x-ui.card-content class="p-6 pt-0">
                <div class="-mt-10 flex flex-wrap items-end justify-between gap-4">
                    <div class="flex items-end gap-4">
                        @if ($profile['image'])
                            <img src="{{ $profile['image'] }}" alt="{{ $profile['name'] }}"
                                 class="size-20 rounded-full object-cover ring-4 ring-card">
                        @else
                            <span class="flex size-20 items-center justify-center rounded-full bg-linear-to-br from-primary to-indigo-900 text-xl font-extrabold text-primary-foreground ring-4 ring-card">
                                {{ mb_strtoupper(mb_substr($profile['name'], 0, 1)) }}
                            </span>
                        @endif

                        <div class="pb-1">
                            <h2 class="text-xl font-extrabold">{{ $profile['name'] }}</h2>

                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <x-ui.badge variant="gold">{{ __('profile.member') }}</x-ui.badge>

                                @if ($profile['headline'])
                                    <x-ui.badge variant="secondary">{{ $profile['headline'] }}</x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="flex items-center gap-1.5 pb-1 text-xs text-muted-foreground">
                        <x-icon name="calendar" class="size-3.5" />
                        {{ __('profile.joined') }} {{ Format::date($profile['createdAt']) }}
                    </p>
                </div>

                @if ($profile['bio'])
                    <p class="mt-5 max-w-2xl text-sm leading-relaxed text-muted-foreground">{{ $profile['bio'] }}</p>
                @endif

                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <x-ui.mini-stat :label="__('orbit.stats.lessonsDone')" :value="$profile['lessonsDone']">
                        <x-slot:icon>
                            <x-icon name="book" class="size-5 text-primary" />
                        </x-slot:icon>
                    </x-ui.mini-stat>

                    <x-ui.mini-stat :label="__('orbit.stats.activePaths')" :value="$profile['enrollmentsCount']">
                        <x-slot:icon>
                            <x-icon name="medal" class="size-5 text-primary" />
                        </x-slot:icon>
                    </x-ui.mini-stat>

                    <x-ui.mini-stat :label="__('profile.certificates')" :value="$profile['certificatesCount']">
                        <x-slot:icon>
                            <x-icon name="award" class="size-5 text-primary" />
                        </x-slot:icon>
                    </x-ui.mini-stat>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section>
            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold">
                <x-icon name="trophy" class="size-5 text-gold" />
                {{ __('profile.achievements') }}
                <x-ui.badge variant="secondary" class="ms-1">{{ $achievements->count() }}</x-ui.badge>
            </h2>

            @if ($achievements->isEmpty())
                <x-ui.card>
                    <x-ui.card-content class="py-14 text-center text-sm text-muted-foreground">
                        {{ __('profile.noAchievements') }}
                    </x-ui.card-content>
                </x-ui.card>
            @else
                <ul class="stagger space-y-3">
                    @foreach ($achievements as $achievement)
                        <li>
                            <x-ui.card class="flex items-start gap-4 transition-colors hover:border-primary/20">
                                <x-ui.card-content class="flex flex-1 items-start gap-4 p-4">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-gold/25 to-gold/5 text-gold ring-1 ring-gold/20 ring-inset">
                                        <x-icon name="sparkles" class="size-5" />
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold">{{ $achievement->title }}</p>

                                        @if ($achievement->description)
                                            <p class="mt-0.5 text-xs text-muted-foreground">{{ $achievement->description }}</p>
                                        @endif
                                    </div>

                                    <span class="shrink-0 text-[11px] text-muted-foreground">
                                        {{ Format::date($achievement->earned_at) }}
                                    </span>
                                </x-ui.card-content>
                            </x-ui.card>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold">
                <x-icon name="award" class="size-5 text-primary" />
                {{ __('profile.certificates') }}
                <x-ui.badge variant="secondary" class="ms-1">{{ $certificates->count() }}</x-ui.badge>
            </h2>

            @if ($certificates->isEmpty())
                <x-ui.card>
                    <x-ui.card-content class="py-14 text-center text-sm text-muted-foreground">
                        {{ __('profile.noCertificates') }}
                    </x-ui.card-content>
                </x-ui.card>
            @else
                <ul class="stagger space-y-3">
                    @foreach ($certificates as $certificate)
                        @php $href = route('student.journey.show', $certificate->journey->slug); @endphp

                        <li>
                            <a href="{{ $href }}" class="group block">
                                <x-ui.card class="relative overflow-hidden transition-all group-hover:border-primary/25 group-hover:shadow-lg group-hover:shadow-primary/10">
                                    <div class="absolute inset-y-0 start-0 w-1 bg-linear-to-b from-gold to-gold/30"></div>

                                    <x-ui.card-content class="p-5 ps-6">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate font-bold transition-colors group-hover:text-primary">
                                                    {{ $certificate->journey?->localizedTitle(app()->getLocale()) ?? '—' }}
                                                </p>

                                                <p class="mt-1 font-mono text-[11px] text-muted-foreground">
                                                    {{ $certificate->serial }}
                                                </p>
                                            </div>

                                            <x-ui.badge variant="gold">{{ Format::date($certificate->issued_at) }}</x-ui.badge>
                                        </div>

                                        <div class="mt-4 flex items-center gap-3">
                                            <x-ui.progress :value="100" class="h-1.5 flex-1"
                                                           indicator-class="bg-linear-to-l from-gold to-gold-soft" />
                                            <span class="text-[11px] font-bold text-gold">100%</span>
                                        </div>

                                        <p class="mt-3 flex items-center gap-1.5 text-[11px] font-bold text-primary opacity-0 transition-opacity group-hover:opacity-100">
                                            <x-icon name="arrow" class="size-3 {{ $dir === 'rtl' ? 'rotate-180' : '' }}" />
                                            {{ __('profile.viewCertificate') }}
                                        </p>
                                    </x-ui.card-content>
                                </x-ui.card>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
