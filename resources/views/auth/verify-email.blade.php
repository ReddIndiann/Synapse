<x-ui.auth-layout
    title="Verify email"
    subtitle="Please confirm your email address to continue"
>
    <div class="mb-5 text-xs text-[var(--text-muted)] leading-relaxed">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 font-semibold text-xs text-emerald-500">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="auth-submit w-full">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="underline text-xs text-[var(--text-secondary)] hover:text-[var(--text)] transition focus:outline-none">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-ui.auth-layout>
