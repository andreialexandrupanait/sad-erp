<x-app-layout>
    <x-slot name="pageTitle">My Services & Rates</x-slot>

    <div class="p-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-3xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __("My Services & Hourly Rates") }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Set your personal hourly rates for each service you offer.") }}
                            </p>
                        </header>

                        @if(session('success'))
                            <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                            </div>
                        @endif

                        <div class="mt-6 space-y-4">
                            @forelse($allServices as $service)
                                @php
                                    $userService = $userServices->get($service->id);
                                    $hasRate = !is_null($userService);
                                @endphp
                                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border {{ $hasRate ? 'border-blue-200 dark:border-blue-800' : 'border-gray-200 dark:border-gray-700' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            @if($service->color_class)
                                                <span class="w-3 h-3 rounded-full {{ $service->badge_class }}"></span>
                                            @endif
                                            <div>
                                                <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $service->name }}</h3>
                                                @if($service->description)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $service->description }}</p>
                                                @endif
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                    {{ __("Default rate:") }} {{ $service->formatted_rate }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            @if($hasRate)
                                                <form method="POST" action="{{ route('profile.services.update', $userService) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="number" name="hourly_rate" value="{{ $userService->hourly_rate }}" step="0.01" min="0" class="w-24 text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <select name="currency" class="text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="RON" {{ $userService->currency === 'RON' ? 'selected' : '' }}>RON</option>
                                                        <option value="EUR" {{ $userService->currency === 'EUR' ? 'selected' : '' }}>EUR</option>
                                                        <option value="USD" {{ $userService->currency === 'USD' ? 'selected' : '' }}>USD</option>
                                                    </select>
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                                                        {{ __("Update") }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('profile.services.destroy', $userService) }}" onsubmit="return confirm('Remove your rate for this service?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('profile.services.store') }}" class="flex items-center gap-2">
                                                    @csrf
                                                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                                                    <input type="number" name="hourly_rate" placeholder="{{ $service->default_rate ?? '0.00' }}" step="0.01" min="0" required class="w-24 text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <select name="currency" class="text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="RON">RON</option>
                                                        <option value="EUR">EUR</option>
                                                        <option value="USD">USD</option>
                                                    </select>
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-600">
                                                        {{ __("Add Rate") }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="mt-2">{{ __("No services available. Ask an administrator to add services to the catalog.") }}</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                        &larr; {{ __("Back to Profile") }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
