<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ $dir ?? config('madarek.direction.' . app()->getLocale(), 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4338ca">

    <title>@yield('title', config('app.name')) · {{ __('brand.name') }} — {{ __('brand.tagline') }}</title>
    <meta name="description" content="@yield('description', 'مدارك — منصة تعليمية غامرة تنقل مفهوم التعلم إلى فضاء من الرحلات المصممة بعناية، بمجتمع حيّ وروّاد موثوقين. Madarek — an immersive Learning Journey Space.')">

    {{-- Apply the persisted theme before first paint so there is no flash. --}}
    <script>
        (function () {
            try {
                if ((localStorage.getItem('madarek_theme') || 'light') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>