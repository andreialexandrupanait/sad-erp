{{-- Catalog Block - Predefined Services (Like Plutio Service Packages) --}}
<div x-show="!previewMode && predefinedServices.length > 0" class="px-6 py-6 bg-slate-50 border-t border-slate-200">
    {{-- Section Heading --}}
    <div class="mb-6">
        <h3 class="text-lg font-bold text-slate-800">{{ __('Service Catalog') }}</h3>
        <div class="h-1 w-12 bg-blue-500 mt-2 rounded"></div>
        <p class="text-sm text-slate-500 mt-2">{{ __('Click on a service to add it to the offer') }}</p>
    </div>

    {{-- Service Package Cards (Plutio Style) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="(service, sIndex) in predefinedServices" :key="service.id">
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-blue-300 transition-all cursor-pointer group"
                 @click="addPredefinedService(sIndex)">
                {{-- Card Header with Icon --}}
                <div class="p-4 border-b border-slate-100">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="flex-grow min-w-0">
                            <h4 class="font-semibold text-slate-800 truncate" x-text="service.name"></h4>
                            <p class="text-xs text-slate-500 line-clamp-2 mt-1" x-text="service.description || ''"></p>
                        </div>
                    </div>
                </div>

                {{-- Price Footer --}}
                <div class="px-4 py-3 bg-slate-50 flex items-center justify-between">
                    <div>
                        <span class="text-lg font-bold text-blue-600" x-text="formatCurrency(service.default_rate)"></span>
                        <span class="text-sm text-slate-400" x-text="service.currency || 'EUR'"></span>
                        <span class="text-xs text-slate-400">/ <span x-text="service.unit || 'buc'"></span></span>
                    </div>
                    <div class="flex items-center gap-1 text-sm font-medium text-blue-600 group-hover:text-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Add') }}
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty Catalog State --}}
    <div x-show="predefinedServices.length === 0" class="text-center py-8">
        <p class="text-slate-500">{{ __('No predefined services available') }}</p>
        <a href="{{ route('settings.services') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">
            {{ __('Add services in settings') }}
        </a>
    </div>
</div>
