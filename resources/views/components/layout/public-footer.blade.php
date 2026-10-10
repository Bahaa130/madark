<footer class="border-t border-border/60 py-8">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 text-center sm:flex-row sm:px-6 lg:px-8">
        <x-layout.logo />

        <p class="text-xs text-muted-foreground">
            © {{ date('Y') }} {{ __('brand.name') }} — {{ __('brand.tagline') }}
        </p>
    </div>
</footer>