<x-guest-layout>
    <div class="mb-4 text-sm text-slate-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <x-ui.button type="submit" class="w-full sm:w-auto">
                {{ __('Resend Verification Email') }}
            </x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center sm:text-left">
            @csrf
            <button type="submit" class="underline text-sm text-slate-600 hover:text-slate-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
