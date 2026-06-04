<x-ui.auth-layout
    title="Confirm access"
    subtitle="Please confirm your password to proceed to this secure area"
>
    <div class="mb-5 text-xs text-[var(--text-muted)] leading-relaxed">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-[var(--text)]" />
            <x-ui.input id="password" icon="password" class="mt-1.5" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <button type="submit" class="auth-submit mt-2">
            {{ __('Confirm Password') }}
        </button>
    </form>
</x-ui.auth-layout>
