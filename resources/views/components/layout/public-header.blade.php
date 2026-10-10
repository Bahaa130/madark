<header class="sticky top-0 z-30 border-b border-border/60 bg-background/70 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
        <x-layout.logo />

        <div class="flex items-center gap-2">
            <x-language-switcher />
            <x-theme-toggle />

            @guest
                <x-ui.button variant="ghost" size="sm" href="{{ route('login') }}" class="hidden sm:inline-flex">
                    {{ __('common.login') }}
                </x-ui.button>

                <x-ui.button variant="gold" size="sm" href="{{ route('register') }}">
                    {{ __('common.register') }}
                </x-ui.button>
            @endguest

            @auth
                <x-ui.button variant="ghost" size="sm" href="{{ route('dashboard') }}" class="hidden sm:inline-flex">
                    {{ __('common.goToDashboard') }}
                </x-ui.button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button variant="gold" size="sm" type="submit">
                        {{ __('common.logout') }}
                    </x-ui.button>
                </form>
            @endauth
        </div>
    </div>
</header>