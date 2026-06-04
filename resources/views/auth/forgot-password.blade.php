<x-ui.auth-layout
    title="Reset password"
    subtitle="Enter your email to receive a password recovery link"
>
    <div class="mb-5 text-xs text-[var(--text-muted)] leading-relaxed">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-[var(--text)]" />
            <x-ui.input id="email" icon="email" class="mt-1.5" type="email" name="email" :value="old('email')" placeholder="you@company.com" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <button type="submit" class="auth-submit mt-2">
            {{ __('Email Reset Link') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-[var(--border)] text-center">
        <p class="text-sm text-[var(--text-secondary)]">
            Remember your password?
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 transition">Sign in</a>
        </p>
    </div>
</x-ui.auth-layout>
