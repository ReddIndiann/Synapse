<x-ui.auth-layout
    title="Create your account"
    subtitle="Start managing your business with Synapse"
    maxWidth="lg"
>
    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-5">
            {{ $errors->first() }}
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" :value="__('First name')" class="text-slate-700" />
                <x-ui.input id="first_name" icon="user" class="mt-1.5" type="text" name="first_name" :value="old('first_name')" placeholder="Jane" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last name')" class="text-slate-700" />
                <x-ui.input id="last_name" icon="user" class="mt-1.5" type="text" name="last_name" :value="old('last_name')" placeholder="Doe" autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-1.5" />
            </div>
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone')" class="text-slate-700" />
            <x-ui.input id="phone" icon="phone" class="mt-1.5" type="text" name="phone" :value="old('phone')" placeholder="+1 (555) 000-0000" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-slate-700" />
            <x-ui.input id="email" icon="email" class="mt-1.5" type="email" name="email" :value="old('email')" placeholder="you@company.com" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" :value="__('Password')" class="text-slate-700" />
                <x-ui.input id="password" icon="password" class="mt-1.5" type="password" name="password" placeholder="Min. 8 characters" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" class="text-slate-700" />
                <x-ui.input id="password_confirmation" icon="password" class="mt-1.5" type="password" name="password_confirmation" placeholder="Repeat password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
            </div>
        </div>

        <button type="submit" class="auth-submit">
            {{ __('Create account') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
        <p class="text-sm text-slate-500">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 transition">Sign in</a>
        </p>
    </div>
</x-ui.auth-layout>
