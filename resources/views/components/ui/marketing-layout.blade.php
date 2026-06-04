@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'Synapse') }}</title>
        
        <!-- Theme Switcher Initializer to block light flashes (Default to Dark) -->
        <script>
            if (localStorage.theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        </script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 bg-[var(--bg)] text-[var(--text)] transition-colors duration-300 relative overflow-x-hidden min-h-screen">
        <div class="aur aur-a top-[-200px] left-[-200px]"></div>
        <div class="aur aur-b bottom-[-200px] right-[-200px]"></div>
        
        <div class="min-h-screen flex flex-col relative z-10">
            <header class="border-b border-[var(--border)] bg-[var(--surface)]/70 backdrop-blur-md sticky top-0 z-50">
                <div class="ui-container h-16 flex items-center justify-between">
                    <x-ui.brand />

                    <div class="flex items-center gap-3">
                        <!-- Theme Toggle Button -->
                        <button onclick="toggleTheme()" class="p-2 rounded-xl border border-[var(--border)] text-[var(--text-secondary)] hover:text-[var(--text)] hover:bg-[var(--bg3)]/50 focus:outline-none transition-colors" aria-label="Toggle theme">
                            <!-- Sun Icon (shows in dark mode) -->
                            <svg class="w-4 h-4 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                            </svg>
                            <!-- Moon Icon (shows in light mode) -->
                            <svg class="w-4 h-4 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

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
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-[var(--border)] py-6 bg-[var(--bg2)]/50">
                <div class="ui-container text-sm text-[var(--text-secondary)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <span>&copy; {{ date('Y') }} Synapse</span>
                    <span>Laravel v{{ app()->version() }}</span>
                </div>
            </footer>
        </div>

        <!-- Global Theme Toggle Script -->
        <script>
            function toggleTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.theme = 'light';
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.theme = 'dark';
                }
            }
        </script>
    </body>
</html>
