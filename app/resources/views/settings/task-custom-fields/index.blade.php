<x-app-layout>
    <x-slot name="pageTitle">{{ __('Task Custom Fields') }}</x-slot>

    <x-slot name="headerActions">
        <a href="{{ route('task-custom-fields.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('Add Custom Field') }}
        </a>
    </x-slot>

    <div class="p-6">
        <div class="max-w-6xl mx-auto">
            <!-- Info Card -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">{{ __('About Custom Fields') }}</p>
                        <p>{{ __('Create custom fields to capture additional information on your tasks. You can reorder fields by dragging them.') }}</p>
                    </div>
                </div>
            </div>

            @if($customFields->isEmpty())
                <!-- Empty State -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">{{ __('No custom fields yet') }}</h3>
                    <p class="text-slate-600 mb-6">{{ __('Get started by creating your first custom field to track additional task information.') }}</p>
                    <a href="{{ route('task-custom-fields.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Create Custom Field') }}
                    </a>
                </div>
            @else
                <!-- Custom Fields List -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                        {{ __('Field Name') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                        {{ __('Type') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                        {{ __('Required') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200" x-data="customFieldsReorder()">
                                @foreach($customFields as $field)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 text-slate-400 mr-2 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                </svg>
                                                <div>
                                                    <div class="text-sm font-medium text-slate-900">{{ $field->name }}</div>
                                                    @if($field->description)
                                                        <div class="text-xs text-slate-500 mt-0.5">{{ $field->description }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($field->type === 'text') bg-blue-100 text-blue-800
                                                @elseif($field->type === 'number') bg-green-100 text-green-800
                                                @elseif($field->type === 'date') bg-purple-100 text-purple-800
                                                @elseif($field->type === 'dropdown') bg-yellow-100 text-yellow-800
                                                @elseif($field->type === 'checkbox') bg-pink-100 text-pink-800
                                                @elseif($field->type === 'email') bg-indigo-100 text-indigo-800
                                                @elseif($field->type === 'url') bg-cyan-100 text-cyan-800
                                                @elseif($field->type === 'phone') bg-orange-100 text-orange-800
                                                @endif">
                                                {{ ucfirst($field->type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($field->is_required)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ __('Required') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                    {{ __('Optional') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($field->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('Active') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                    {{ __('Inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('task-custom-fields.edit', $field) }}" class="text-primary-600 hover:text-primary-700">
                                                    {{ __('Edit') }}
                                                </a>
                                                <form action="{{ route('task-custom-fields.destroy', $field) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this custom field? All associated data will be lost.') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-700">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    function customFieldsReorder() {
        return {
            // Future: Add drag-and-drop reordering functionality here
        };
    }
    </script>
    @endpush
</x-app-layout>
