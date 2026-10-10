@extends('layouts.workspace')

@section('title', __('builder.newJourney'))

@section('workspace')
    <x-layout.page-header
        :title="__('builder.newJourney')"
        :subtitle="__('builder.newJourneySubtitle')">
        <x-slot:icon>
            <x-icon name="sparkles" class="size-6" />
        </x-slot:icon>
        <x-slot:actions>
            <x-ui.button variant="ghost" size="sm" tag="a" :href="route('instructor.journeys')">
                <x-icon name="arrow" class="size-4" />
                <span>{{ __('common.back') }}</span>
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.card class="mx-auto max-w-3xl">
        <x-ui.card-content class="p-6 sm:p-8">
            <form method="POST" action="{{ route('instructor.journeys.store') }}" class="space-y-5">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.title') }} *</label>
                        <x-ui.input name="title" required minlength="3" maxlength="190" value="{{ old('title') }}"
                                    placeholder="{{ __('builder.titlePlaceholder') }}" />
                        @error('title')<p class="text-xs font-semibold text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.titleEn') }}</label>
                        <x-ui.input name="title_en" maxlength="190" value="{{ old('title_en') }}"
                                    placeholder="English title (optional)" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.language') }}</label>
                        <select name="language" class="h-11 w-full rounded-xl border border-input bg-card px-4 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            <option value="ar" @selected(old('language', 'ar') === 'ar')>العربية</option>
                            <option value="en" @selected(old('language') === 'en')>English</option>
                        </select>
                    </div>

                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.tagline') }}</label>
                        <x-ui.input name="tagline" maxlength="255" value="{{ old('tagline') }}"
                                    placeholder="{{ __('builder.taglinePlaceholder') }}" />
                    </div>

                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.description') }}</label>
                        <textarea name="description" rows="5" maxlength="5000"
                                  class="w-full rounded-xl border border-input bg-card px-4 py-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                  placeholder="{{ __('builder.descriptionPlaceholder') }}">{{ old('description') }}</textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('common.level') }}</label>
                        <select name="level" class="h-11 w-full rounded-xl border border-input bg-card px-4 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            @foreach (['BEGINNER', 'INTERMEDIATE', 'ADVANCED', 'MASTER'] as $lvl)
                                <option value="{{ $lvl }}" @selected(old('level', 'BEGINNER') === $lvl)>{{ __('levels.'.$lvl) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.pricing') }}</label>
                        <div class="flex h-11 items-center gap-4 rounded-xl border border-input bg-card px-4">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="is_free" value="1" @checked(! old('is_free') || old('is_free') === '1')
                                       class="size-4 accent-indigo-600" onchange="document.getElementById('priceWrap').classList.add('hidden')">
                                <span>{{ __('builder.free') }}</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="is_free" value="0" @checked(old('is_free') === '0' || old('price') > 0)
                                       class="size-4 accent-indigo-600" onchange="document.getElementById('priceWrap').classList.remove('hidden')">
                                <span>{{ __('builder.paid') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1.5 {{ old('price') > 0 ? '' : 'hidden' }}" id="priceWrap">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.price') }} ($)</label>
                        <x-ui.input name="price" type="number" min="0" max="9999" value="{{ old('price', 0) }}" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-muted-foreground">{{ __('builder.coverUrl') }}</label>
                        <x-ui.input name="cover_image" type="url" maxlength="500" value="{{ old('cover_image') }}"
                                    placeholder="https://…" />
                        @error('cover_image')<p class="text-xs font-semibold text-destructive">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-border pt-5">
                    <x-ui.button variant="ghost" tag="a" :href="route('instructor.journeys')">{{ __('common.cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="gold">
                        <x-icon name="sparkles" class="size-4" />
                        <span>{{ __('builder.createAndBuild') }}</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>
@endsection
