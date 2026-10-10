@props([
    'nav' => [],
    'bottomNav' => null,
])

<div class="min-h-dvh cosmic-bg">
    <x-layout.sidebar :nav="$nav" />

    <div class="lg:ps-72">
        <x-layout.topbar />

        <main class="mx-auto w-full max-w-7xl px-4 pt-6 pb-28 sm:px-6 lg:px-8 lg:pb-12">
            {{ $slot }}
        </main>
    </div>

    @if (count($bottomNav ?? $nav) > 0)
        <x-layout.bottom-nav :nav="$bottomNav ?? $nav" />
    @endif
</div>