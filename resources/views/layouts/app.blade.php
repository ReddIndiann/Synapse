<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Synapse') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,650,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="{ sidebarOpen: false }" class="font-sans antialiased text-slate-800 bg-slate-50/50">
        <div class="min-h-screen flex flex-col">
            <!-- Sidebar (Includes desktop and mobile drawer versions) -->
            @include('layouts.navigation')

            <!-- Main Layout Viewport -->
            <div class="lg:pl-64 flex flex-col flex-1 min-h-screen">
                <!-- Mobile top navigation bar -->
                <header class="flex lg:hidden items-center justify-between h-16 bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-5 sticky top-0 z-30">
                    <button @click="sidebarOpen = true" class="p-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold shadow-sm">
                            S
                        </div>
                        <span class="font-semibold text-slate-900 tracking-tight text-sm">Synapse</span>
                    </a>

                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shadow-sm">
                        {{ substr(Auth::user()->name, 0, 2) }}
                    </div>
                </header>

                <!-- Page Header (Dynamic Breadcrumb or Navigation) -->
                @isset($header)
                    <div class="bg-white/40 border-b border-slate-100 py-5 px-5 sm:px-8 lg:px-10 backdrop-blur-md">
                        {{ $header }}
                    </div>
                @endisset

                <!-- Main Content Area -->
                <main class="flex-1 px-5 sm:px-8 lg:px-10">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
