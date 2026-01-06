<x-app-layout>
    <x-slot name="pageTitle">My Services & Rates</x-slot>

    <div class="p-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-3xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __("My Services & Hourly Rates") }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Set your personal hourly rates for each service you offer.") }}
                            </p>
                        </header>

                        @if(session('success'))
                            <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                                <p class="text-sm text-green-800">{{ session('success') }}</p>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                                <p class="text-sm text-red-800">{{ session('error') }}</p>
                            </div>
                        @endif

                        <div class="mt-6 space-y-4">
                            @forelse($allServices as $service)
                                @php
                                    $userService = $userServices->get($service->id);
                                    $hasRate = !is_null($userService);
                                @endphp
                                <div class="p-4 bg-gray-50 rounded-lg border {{ $hasRate ? 'border-blue-200' : 'border-gray-200' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            @if($service->color_class)
                                                <span class="w-3 h-3 rounded-full {{ $service->badge_class }}"></span>
                                            @endif
                                            <div>
                                                <h3 class="font-medium text-gray-900">{{ $service->name }}</h3>
                                                @if($service->description)
                                                    <p class="text-sm text-gray-500">{{ $service->description }}</p>
                                                @endif
                                                <p class="text-xs text-gray-400 mt-1">
                                                    {{ __("Default rate:") }} {{ $service->formatted_rate }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            @if($hasRate)
                                                <form method="POST" action="{{ route('profile.services.update', $userService) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <label for="hourly_rate_{{ $service->id }}" class="sr-only">{{ __("Hourly rate for") }} {{ $service->name }}</label>
                                                    <input type="number" id="hourly_rate_{{ $service->id }}" name="hourly_rate" value="{{ $userService->hourly_rate }}" step="0.01" min="0" class="w-24 text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <label for="currency_{{ $service->id }}" class="sr-only">{{ __("Currency for") }} {{ $service->name }}</label>
                                                    <select id="currency_{{ $service->id }}" name="currency" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="RON" {{ $userService->currency === 'RON' ? 'selected' : '' }}>RON</option>
                                                        <option value="EUR" {{ $userService->currency === 'EUR' ? 'selected' : '' }}>EUR</option>
                                                        <option value="USD" {{ $userService->currency === 'USD' ? 'selected' : '' }}>USD</option>
                                                    </select>
                                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700 min-h-[44px]">
                                                        {{ __("Update") }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('profile.services.destroy', $userService) }}" onsubmit="return confirm('Remove your rate for this service?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2.5 text-red-600 hover:text-red-800 min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="{{ __('Remove rate for') }} {{ $service->name }}">
                                                        <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('profile.services.store') }}" class="flex items-center gap-2">
                                                    @csrf
                                                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                                                    <label for="new_hourly_rate_{{ $service->id }}" class="sr-only">{{ __("Hourly rate for") }} {{ $service->name }}</label>
                                                    <input type="number" id="new_hourly_rate_{{ $service->id }}" name="hourly_rate" placeholder="{{ $service->default_rate ?? '0.00' }}" step="0.01" min="0" required class="w-24 text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <label for="new_currency_{{ $service->id }}" class="sr-only">{{ __("Currency for") }} {{ $service->name }}</label>
                                                    <select id="new_currency_{{ $service->id }}" name="currency" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="RON">RON</option>
                                                        <option value="EUR">EUR</option>
                                                        <option value="USD">USD</option>
                                                    </select>
                                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 min-h-[44px]">
                                                        {{ __("Add Rate") }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
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

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        &larr; {{ __("Back to Profile") }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
