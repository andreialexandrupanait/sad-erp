<x-app-layout>
    <x-slot name="pageTitle">{{ __("Services Catalog") }}</x-slot>

    <div class="p-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __("Services Catalog") }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Manage the services your organization offers. Users can set their own rates for each service.") }}
                            </p>
                        </div>
                        <button type="button" onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __("Add Service") }}
                        </button>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                        </div>
                    @endif

                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Service") }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Default Rate") }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Users") }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Status") }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Actions") }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="services-list">
                                @forelse($services as $service)
                                    <tr data-id="{{ $service->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                @if($service->color_class)
                                                    <span class="w-3 h-3 rounded-full {{ $service->badge_class }}"></span>
                                                @endif
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $service->name }}</div>
                                                    @if($service->description)
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($service->description, 50) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $service->formatted_rate }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $service->user_services_count }} {{ trans_choice('{0} users|{1} user|[2,*] users', $service->user_services_count) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($service->is_active)
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">{{ __("Active") }}</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-full">{{ __("Inactive") }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" onclick="openEditModal({{ json_encode($service) }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">{{ __("Edit") }}</button>
                                            <form method="POST" action="{{ route('settings.services.destroy', $service) }}" class="inline" onsubmit="return confirm('{{ __("Are you sure you want to delete this service?") }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">{{ __("Delete") }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                            <p class="mt-2">{{ __("No services defined yet.") }}</p>
                                            <a href="#" onclick="openAddModal(); return false;" class="mt-4 inline-block text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                {{ __("Add your first service") }} &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="serviceModal" class="fixed inset-0 z-50 hidden" x-data="{ open: false }">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <form id="serviceForm" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">

                        <h3 id="modalTitle" class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __("Add Service") }}</h3>

                        <div class="space-y-4">
                            <div>
                                <x-ui.label for="name">{{ __("Service Name") }} *</x-ui.label>
                                <x-ui.input type="text" name="name" id="name" required />
                            </div>

                            <div>
                                <x-ui.label for="description">{{ __("Description") }}</x-ui.label>
                                <textarea name="description" id="description" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="default_rate">{{ __("Default Hourly Rate") }}</x-ui.label>
                                    <x-ui.input type="number" name="default_rate" id="default_rate" step="0.01" min="0" />
                                </div>
                                <div>
                                    <x-ui.label for="currency">{{ __("Currency") }}</x-ui.label>
                                    <select name="currency" id="currency" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="RON">RON</option>
                                        <option value="EUR">EUR</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <x-ui.label for="color_class">{{ __("Color") }}</x-ui.label>
                                <select name="color_class" id="color_class" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">{{ __("No color") }}</option>
                                    <option value="blue">{{ __("Blue") }}</option>
                                    <option value="green">{{ __("Green") }}</option>
                                    <option value="red">{{ __("Red") }}</option>
                                    <option value="yellow">{{ __("Yellow") }}</option>
                                    <option value="purple">{{ __("Purple") }}</option>
                                    <option value="orange">{{ __("Orange") }}</option>
                                    <option value="pink">{{ __("Pink") }}</option>
                                    <option value="cyan">{{ __("Cyan") }}</option>
                                </select>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500">
                                <label for="is_active" class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __("Active") }}</label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                {{ __("Cancel") }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ __("Save") }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = '{{ __("Add Service") }}';
            document.getElementById('serviceForm').action = '{{ route("settings.services.store") }}';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('name').value = '';
            document.getElementById('description').value = '';
            document.getElementById('default_rate').value = '';
            document.getElementById('currency').value = 'RON';
            document.getElementById('color_class').value = '';
            document.getElementById('is_active').checked = true;
            document.getElementById('serviceModal').classList.remove('hidden');
        }

        function openEditModal(service) {
            document.getElementById('modalTitle').textContent = '{{ __("Edit Service") }}';
            document.getElementById('serviceForm').action = '{{ url("settings/services") }}' + '/'+service.id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('name').value = service.name;
            document.getElementById('description').value = service.description || '';
            document.getElementById('default_rate').value = service.default_rate || '';
            document.getElementById('currency').value = service.currency;
            document.getElementById('color_class').value = service.color_class || '';
            document.getElementById('is_active').checked = service.is_active;
            document.getElementById('serviceModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('serviceModal').classList.add('hidden');
        }

        document.getElementById('serviceModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</x-app-layout>
