<x-app-layout>
    <x-slot name="pageTitle">Setari</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
        <div class="p-6">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-slate-900">Setari aplicatie</h2>
                <p class="text-sm text-slate-500 mt-1">Configureaza preferintele aplicatiei</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                <form method="POST" action="{{ route('settings.application.update') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nume aplicatie</label>
                            <input type="text" name="app_name" value="{{ $appSettings['app_name'] }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Limba</label>
                            <select name="language" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                <option value="en" {{ $appSettings['language'] === 'en' ? 'selected' : '' }}>English</option>
                                <option value="ro" {{ $appSettings['language'] === 'ro' ? 'selected' : '' }}>Română</option>
                            </select>
                        </div>
                    </div>

                    <!-- Hidden fields for required validation -->
                    <input type="hidden" name="theme_mode" value="{{ $appSettings['theme_mode'] }}">
                    <input type="hidden" name="primary_color" value="{{ $appSettings['primary_color'] }}">
                    <input type="hidden" name="timezone" value="{{ $appSettings['timezone'] }}">
                    <input type="hidden" name="date_format" value="{{ $appSettings['date_format'] }}">

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">
                            Salveaza modificari
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</x-app-layout>
