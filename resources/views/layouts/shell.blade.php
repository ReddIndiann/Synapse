<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Synapse') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-[var(--text)] overflow-x-hidden min-h-screen relative">
        <div class="aur aur-a top-[-200px] left-[-200px]"></div>
        <div class="aur aur-b bottom-[-200px] right-[-200px]"></div>
        <div class="min-h-screen flex relative z-10">
            {{-- Sidebar --}}
            <aside class="hidden lg:flex lg:flex-col w-64 border-r border-slate-200 bg-white/80 backdrop-blur-sm shrink-0">
                <div class="h-16 flex items-center px-6 border-b border-slate-200">
                    <x-ui.brand size="sm" />
                </div>

                <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                    <x-ui.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
                        Dashboard
                    </x-ui.nav-link>

                    <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Assistant</p>
                    <x-ui.nav-link :href="route('assistant.chat')" :active="request()->routeIs('assistant.chat*')" icon="sparkles">
                        AI Chat
                    </x-ui.nav-link>
                    <x-ui.nav-link :href="route('assistant.tasks.index')" :active="request()->routeIs('assistant.tasks.*')" icon="clipboard">
                        Tasks
                    </x-ui.nav-link>

                    <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Accounting</p>
                    <x-ui.nav-link :href="route('accounting.transactions.index')" :active="request()->routeIs('accounting.transactions.*')" icon="wallet">
                        Transactions
                    </x-ui.nav-link>
                    <x-ui.nav-link :href="route('accounting.budgets.index')" :active="request()->routeIs('accounting.budgets.*')" icon="wallet">
                        Budgets
                    </x-ui.nav-link>
                    <x-ui.nav-link :href="route('accounting.reports.index')" :active="request()->routeIs('accounting.reports.*')" icon="chart">
                        Reports
                    </x-ui.nav-link>

                    <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Distribution</p>
                    <x-ui.nav-link :href="route('distribution.media.index')" :active="request()->routeIs('distribution.media.*')" icon="photo">
                        Media Library
                    </x-ui.nav-link>
                    <x-ui.nav-link :href="route('distribution.publish.index')" :active="request()->routeIs('distribution.publish.*')" icon="send">
                        Publish Queue
                    </x-ui.nav-link>

                    @role('admin')
                        <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Admin</p>
                        <x-ui.nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" icon="users">
                            Users
                        </x-ui.nav-link>
                        <x-ui.nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')" icon="shield">
                            Roles
                        </x-ui.nav-link>
                        <x-ui.nav-link :href="route('ui-kit')" :active="request()->routeIs('ui-kit')" icon="palette">
                            UI Template
                        </x-ui.nav-link>
                    @endrole
                </nav>

                <div class="p-4 border-t border-slate-200">
                    <div class="flex items-center gap-3 px-2">
                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <x-ui.button :href="route('profile.edit')" variant="ghost" size="sm" class="flex-1">Profile</x-ui.button>
                        <form method="POST" action="{{ route('logout') }}" class="flex-1">
                            @csrf
                            <x-ui.button type="submit" variant="secondary" size="sm" class="w-full">Logout</x-ui.button>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- Main --}}
            <div class="flex-1 flex flex-col min-w-0">
                {{-- Mobile top bar --}}
                <header class="lg:hidden bg-white/80 backdrop-blur-sm border-b border-slate-200">
                    @include('layouts.navigation')
                </header>

                @isset($header)
                    <header class="hidden lg:block bg-white/80 backdrop-blur-sm border-b border-slate-200">
                        <div class="ui-container py-6">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
