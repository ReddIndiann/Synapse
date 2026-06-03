@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'Synapse') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900">
        <div class="min-h-screen flex flex-col">
            <header class="border-b border-slate-200/80 bg-white/70 backdrop-blur-sm sticky top-0 z-10">
                <div class="ui-container h-16 flex items-center justify-between">
                    <x-ui.brand />

                    @if (Route::has('login'))
                        <nav class="flex items-center gap-3">
                            @auth
                                <x-ui.button :href="route('dashboard')" variant="primary" size="sm">Dashboard</x-ui.button>
                            @else
                                <x-ui.button :href="route('login')" variant="secondary" size="sm">Log in</x-ui.button>
                                @if (Route::has('register'))
                                    <x-ui.button :href="route('register')" variant="primary" size="sm">Register</x-ui.button>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-slate-200/80 py-6">
                <div class="ui-container text-sm text-slate-500 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <span>&copy; {{ date('Y') }} Synapse</span>
                    <span>Laravel v{{ app()->version() }}</span>
                </div>
            </footer>
        </div>
    </body>
</html>
