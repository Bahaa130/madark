@extends('layouts.app')

@section('title', __('common.login'))

@section('content')
<div class="cosmic-bg flex min-h-dvh flex-col">
    <header class="flex items-center justify-between px-4 py-5 sm:px-8">
        <x-layout.logo />
        <div class="flex items-center gap-2">
            <x-language-switcher />
            <x-theme-toggle />
        </div>
    </header>

    <main class="flex flex-1 items-center justify-center px-4 pb-20">
        <div class="animate-rise w-full max-w-md">
            <div class="rounded-3xl border border-border bg-card/80 p-7 shadow-2xl shadow-black/10 backdrop-blur-xl sm:p-9">
                <div class="mb-7 text-center">
                    <span class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-linear-to-br from-primary to-indigo-900 text-2xl shadow-lg shadow-primary/25">
                        ✦
                    </span>
                    <h1 class="text-2xl font-extrabold tracking-tight">{{ __('auth.loginTitle') }}</h1>
                    <p class="mt-1.5 text-sm text-muted-foreground">{{ __('auth.loginSubtitle') }}</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="email" class="text-xs font-bold text-muted-foreground">
                            {{ __('common.email') }}
                        </label>
                        <x-ui.input id="email" name="email" type="email" autocomplete="email"
                                   value="{{ old('email') }}" required autofocus
                                   placeholder="you@example.com" />
                        @error('email')
                            <p class="flex items-center gap-1.5 text-xs font-semibold text-destructive">
                                {{-- AlertCircle --}}
                                <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 8v4M12 16h.01" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="text-xs font-bold text-muted-foreground">
                            {{ __('common.password') }}
                        </label>
                        <x-ui.input id="password" name="password" type="password"
                                   autocomplete="current-password" required
                                   placeholder="••••••••" />
                        @error('password')
                            <p class="text-xs font-semibold text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                        <input type="checkbox" name="remember" value="1"
                               class="size-4 rounded border-input accent-primary">
                        {{ $dir === 'rtl' ? 'تذكّرني' : 'Remember me' }}
                    </label>

                    <x-ui.button type="submit" class="w-full">
                        {{ __('common.login') }}
                    </x-ui.button>
                </form>

                <div class="my-5 flex items-center gap-3">
                    <span class="h-px flex-1 bg-border"></span>
                    <span class="text-xs font-medium text-muted-foreground">{{ __('auth.or') }}</span>
                    <span class="h-px flex-1 bg-border"></span>
                </div>

                <x-ui.button variant="outline" class="w-full" disabled>
                    <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1Z" />
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.65l-3.57-2.77c-.99.66-2.25 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z" />
                        <path fill="#FBBC05" d="M5.84 14.11a6.6 6.6 0 0 1 0-4.22V7.05H2.18a11 11 0 0 0 0 9.9l3.66-2.84Z" />
                        <path fill="#EA4335" d="M12 4.75c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 1.46 14.97.5 12 .5A11 11 0 0 0 2.18 7.05l3.66 2.84C6.71 6.68 9.14 4.75 12 4.75Z" />
                    </svg>
                    {{ __('auth.google') }}
                </x-ui.button>

                <p class="mt-6 text-center text-sm text-muted-foreground">
                    {{ __('auth.noAccount') }}
                    <a href="{{ route('register', $callbackUrl !== '/dashboard' ? ['callbackUrl' => $callbackUrl] : []) }}"
                       class="font-bold text-primary hover:underline">
                        {{ __('common.register') }}
                    </a>
                </p>
            </div>
        </div>
    </main>
</div>
@endsection