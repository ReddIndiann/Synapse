@props(['title' => null, 'subtitle' => null, 'maxWidth' => 'md'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'Synapse') }}</title>
        
        <!-- Theme Switcher Initializer (Default to Dark) -->
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
    <body class="font-sans text-[var(--text)] antialiased">
        <div class="auth-shell lg:grid lg:grid-cols-2">
            {{-- Brand panel --}}
            <div class="auth-panel-brand">
                <div class="auth-grid"></div>
                <div class="auth-orb w-72 h-72 bg-violet-400/30 -top-20 -right-20"></div>
                <div class="auth-orb w-96 h-96 bg-indigo-300/20 bottom-0 left-1/4"></div>

                <div class="relative z-10">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                        <div class="w-11 h-11 rounded-2xl bg-white text-indigo-700 flex items-center justify-center font-bold text-lg shadow-lg">S</div>
                        <span class="font-semibold text-white text-lg tracking-tight">Synapse</span>
                    </a>
                </div>

                <div class="relative z-10 max-w-lg">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-200">AI-First Business Platform</p>
                    <h1 class="mt-4 text-4xl xl:text-5xl font-bold tracking-tight leading-[1.1]">
                        One platform.<br>Every operation.
                    </h1>
                    <p class="mt-5 text-lg text-indigo-100/90 leading-relaxed">
                        Automate tasks, track finances, and publish content — all from a single intelligent workspace.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-2">
                        <span class="auth-feature-pill">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            AI Assistant
                        </span>
                        <span class="auth-feature-pill">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Accounting
                        </span>
                        <span class="auth-feature-pill">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Distribution
                        </span>
                    </div>
                </div>

                <p class="relative z-10 text-sm text-indigo-200/80">&copy; {{ date('Y') }} Synapse. All rights reserved.</p>
            </div>

            {{-- Form panel --}}
            <div class="relative flex flex-col justify-center px-5 sm:px-8 lg:px-12 xl:px-16 py-12 lg:py-16">
                {{-- Mobile brand --}}
                <div class="lg:hidden mb-10 text-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold">S</div>
                        <span class="font-semibold text-[var(--text)] text-lg">Synapse</span>
                    </a>
                </div>

                <div @class([
                    'mx-auto w-full relative z-10',
                    'max-w-[420px]' => $maxWidth === 'md',
                    'max-w-lg' => $maxWidth === 'lg',
                ])>
                    <div class="auth-card">
                        @if ($title)
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-bold text-[var(--text)] tracking-tight">{{ $title }}</h2>
                                @if ($subtitle)
                                    <p class="mt-2 text-sm text-[var(--text-muted)] leading-relaxed">{{ $subtitle }}</p>
                                @endif
                            </div>
                        @endif

                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs text-[var(--text-secondary)]">
                        Secured with enterprise-grade encryption
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
