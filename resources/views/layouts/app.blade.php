<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Synapse') }}</title>

        <!-- Theme Switcher Initializer to block light flashes (Default to Dark) -->
        <script>
            if (localStorage.theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="{ sidebarOpen: false }" class="font-sans antialiased text-[var(--text)] transition-colors duration-300 overflow-x-hidden min-h-screen relative">
        <div class="aur aur-a top-[-200px] left-[-200px]"></div>
        <div class="aur aur-b bottom-[-200px] right-[-200px]"></div>
        <div class="min-h-screen flex flex-col relative z-10">
            <!-- Sidebar -->
            @include('layouts.navigation')

            <!-- Main Layout Viewport -->
            <div class="lg:pl-64 flex flex-col flex-1 min-h-screen">
                <!-- Mobile top navigation bar -->
                <header class="flex lg:hidden items-center justify-between h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 px-5 sticky top-0 z-30 transition-colors duration-300">
                    <button @click="sidebarOpen = true" class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold shadow-sm">
                            S
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white tracking-tight text-sm">Synapse</span>
                    </a>

                    <!-- Mobile Theme Toggler Button -->
                    <button onclick="toggleTheme()" class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none">
                        <!-- Sun Icon (shows in dark mode) -->
                        <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                        </svg>
                        <!-- Moon Icon (shows in light mode) -->
                        <svg class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </header>

                <!-- Page Header -->
                @isset($header)
                    <div class="bg-white/40 dark:bg-slate-900/10 border-b border-slate-100 dark:border-slate-800/60 py-5 px-5 sm:px-8 lg:px-10 backdrop-blur-md transition-colors duration-300">
                        {{ $header }}
                    </div>
                @endisset

                <!-- Main Content Area -->
                <main class="flex-1 px-5 sm:px-8 lg:px-10 py-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Floating Theme Switcher Button -->
        <div class="fixed bottom-6 right-6 z-50">
            <button onclick="toggleTheme()" class="w-12 h-12 rounded-full bg-[var(--surface)]/80 border border-[var(--border)] text-[var(--text)] hover:text-[var(--pur)] shadow-[0_8px_30px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_30px_rgba(0,0,0,0.4)] flex items-center justify-center transition-all hover:scale-110 active:scale-95 focus:outline-none backdrop-blur-md" title="Toggle Light/Dark Mode">
                <!-- Sun Icon (shows in dark mode) -->
                <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                </svg>
                <!-- Moon Icon (shows in light mode) -->
                <svg class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
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
                window.dispatchEvent(new Event('theme-changed'));
            }
        </script>
    </body>
</html>
