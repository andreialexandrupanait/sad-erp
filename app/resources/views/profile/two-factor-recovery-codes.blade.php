<x-app-layout>
    <x-slot name="pageTitle">Recovery Codes</x-slot>

    <div class="p-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __("Recovery Codes") }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Store these recovery codes in a secure location. Each code can only be used once to regain access to your account if you lose your authenticator device.") }}
                            </p>
                        </header>

                        @if(isset($regenerated) && $regenerated)
                            <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                    {{ __("New recovery codes have been generated. Your old codes are no longer valid.") }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                                @foreach($recoveryCodes as $code)
                                    <div class="px-3 py-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                                        {{ $code }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <button type="button" onclick="copyRecoveryCodes()" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __("Copy Codes") }}
                            </button>
                            <a href="{{ route('profile.two-factor') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                                {{ __("Done") }}
                            </a>
                        </div>

                        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                            {{ __("Warning: These codes will only be shown once. Make sure to save them before leaving this page.") }}
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyRecoveryCodes() {
            const codes = @json($recoveryCodes);
            navigator.clipboard.writeText(codes.join('\\n')).then(() => {
                alert('Recovery codes copied to clipboard!');
            });
        }
    </script>
</x-app-layout>
