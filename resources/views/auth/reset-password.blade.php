<x-ui.auth-layout
    title="Update password"
    subtitle="Choose a new secure password for your workspace"
>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-[var(--text)]" />
            <x-ui.input id="email" icon="email" class="mt-1.5" type="email" name="email" :value="old('email', $request->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('New password')" class="text-[var(--text)]" />
            <x-ui.input id="password" icon="password" class="mt-1.5" type="password" name="password" placeholder="Min. 8 characters" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm new password')" class="text-[var(--text)]" />
            <x-ui.input id="password_confirmation" icon="password" class="mt-1.5" type="password" name="password_confirmation" placeholder="Confirm password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button type="submit" class="auth-submit mt-2">
            {{ __('Update Password') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </form>
</x-ui.auth-layout>
