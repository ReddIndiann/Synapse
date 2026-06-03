<x-ui.auth-layout
    title="Welcome back"
    subtitle="Sign in to your Synapse workspace"
>
    <x-auth-session-status class="mb-5" :status="session('status')" />

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-5">
            {{ $errors->first() }}
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-slate-700" />
            <x-ui.input
                id="email"
                icon="email"
                class="mt-1.5"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="you@company.com"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="text-slate-700" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition" href="{{ route('password.request') }}">
                        {{ __('Forgot?') }}
                    </a>
                @endif
            </div>
            <x-ui.input
                id="password"
                icon="password"
                class="mt-1.5"
                type="password"
                name="password"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <label for="remember_me" class="flex items-center gap-2.5 cursor-pointer select-none">
            <input
                id="remember_me"
                type="checkbox"
                name="remember"
                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30 focus:ring-offset-0"
            >
            <span class="text-sm text-slate-600">{{ __('Keep me signed in') }}</span>
        </label>

        <button type="submit" class="auth-submit">
            {{ __('Sign in') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </form>

    @if (Route::has('register'))
        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <p class="text-sm text-slate-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 transition">
                    Create one free
                </a>
            </p>
        </div>
    @endif
</x-ui.auth-layout>
