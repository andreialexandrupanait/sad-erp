@props(['task' => null, 'action', 'method' => 'POST', 'lists' => [], 'services' => [], 'users' => [], 'taskStatuses' => [], 'taskPriorities' => [], 'customFields' => [], 'selectedListId' => null])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{
    listId: '{{ old('list_id', $task->list_id ?? $selectedListId ?? '') }}',
    serviceId: '{{ old('service_id', $task->service_id ?? '') }}',
    timeTracked: '{{ old('time_tracked', $task->time_tracked ?? 0) }}',
    amount: '{{ old('amount', $task->amount ?? '') }}',
    totalAmount: '{{ old('total_amount', $task->total_amount ?? 0) }}',

    calculateTotal() {
        const time = parseFloat(this.timeTracked) || 0;
        const rate = parseFloat(this.amount) || 0;
        this.totalAmount = ((time / 60) * rate).toFixed(2);
    }
}" x-init="
    $watch('timeTracked', () => calculateTotal());
    $watch('amount', () => calculateTotal());
">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="space-y-6">
                <!-- Task Name (Required) - 100% -->
                <div class="field-wrapper">
                    <x-ui.label for="name">
                        {{ __('Task Name') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="name"
                            id="name"
                            required
                            value="{{ old('name', $task->name ?? '') }}"
                            placeholder="{{ __('Enter task name') }}"
                        />
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description - 100% -->
                <div class="field-wrapper">
                    <x-ui.label for="description">
                        {{ __('Description') }}
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea
                            name="description"
                            id="description"
                            rows="4"
                            placeholder="{{ __('Enter task description...') }}"
                        >{{ old('description', $task->description ?? '') }}</x-ui.textarea>
                    </div>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- List (Client) 50% | Status 50% -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- List (Client) (Required) -->
                    <div class="field-wrapper">
                        <x-ui.label for="list_id">
                            {{ __('List (Client)') }} <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="list_id" id="list_id" required x-model="listId">
                                <option value="">{{ __('Select a list') }}</option>
                                @foreach($lists as $list)
                                    <option value="{{ $list->id }}" {{ old('list_id', $task->list_id ?? $selectedListId) == $list->id ? 'selected' : '' }}>
                                        {{ $list->name }}@if($list->client) - {{ $list->client->name }}@endif
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        @error('list_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status (Required) -->
                    <div class="field-wrapper">
                        <x-ui.label for="status_id">
                            {{ __('Status') }} <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="status_id" id="status_id" required>
                                <option value="">{{ __('Select status') }}</option>
                                @foreach($taskStatuses as $status)
                                    <option value="{{ $status->id }}" {{ old('status_id', $task->status_id ?? '') == $status->id ? 'selected' : '' }}>
                                        {{ $status->label }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        @error('status_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Assigned To 50% | Due Date 50% -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Assigned To -->
                    <div class="field-wrapper">
                        <x-ui.label for="assigned_to">
                            {{ __('Assigned To') }}
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="assigned_to" id="assigned_to">
                                <option value="">{{ __('Unassigned') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to ?? '') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        @error('assigned_to')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start Date -->
                    <div class="field-wrapper">
                        <x-ui.label for="start_date">
                            {{ __('Start Date') }}
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input
                                type="date"
                                name="start_date"
                                id="start_date"
                                value="{{ old('start_date', $task ? $task->start_date?->format('Y-m-d') : '') }}"
                            />
                        </div>
                        @error('start_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Due Date -->
                    <div class="field-wrapper">
                        <x-ui.label for="due_date">
                            {{ __('Due Date') }}
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input
                                type="date"
                                name="due_date"
                                id="due_date"
                                value="{{ old('due_date', $task ? $task->due_date?->format('Y-m-d') : '') }}"
                            />
                        </div>
                        @error('due_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Service -->
                <div class="field-wrapper">
                    <x-ui.label for="service_id">
                        {{ __('Service') }}
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="service_id" id="service_id" x-model="serviceId">
                            <option value="">{{ __('No service') }}</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id', $task->service_id ?? '') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} ({{ number_format($service->default_hourly_rate, 2) }} RON/h)
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('service_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Time Estimate 50% | Time Tracked 50% -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Time Estimate (minutes) -->
                    <div class="field-wrapper">
                        <x-ui.label for="time_estimate">
                            {{ __('Time Estimate (minutes)') }}
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input
                                type="number"
                                name="time_estimate"
                                id="time_estimate"
                                min="0"
                                value="{{ old('time_estimate', $task->time_estimate ?? '') }}"
                                placeholder="0"
                            />
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ __('Estimated time to complete this task') }}</p>
                        @error('time_estimate')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Time Tracked (minutes) -->
                    <div class="field-wrapper">
                        <x-ui.label for="time_tracked">
                            {{ __('Time Tracked (minutes)') }}
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input
                                type="number"
                                name="time_tracked"
                                id="time_tracked"
                                min="0"
                                x-model="timeTracked"
                                value="{{ old('time_tracked', $task->time_tracked ?? 0) }}"
                                placeholder="0"
                            />
                        </div>
                        @error('time_tracked')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Hourly Rate 50% | Total Amount 50% (readonly) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Hourly Rate -->
                    <div class="field-wrapper">
                        <x-ui.label for="amount">
                            {{ __('Hourly Rate (RON)') }}
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input
                                type="number"
                                step="0.01"
                                name="amount"
                                id="amount"
                                min="0"
                                x-model="amount"
                                value="{{ old('amount', $task->amount ?? '') }}"
                                placeholder="0.00"
                            />
                        </div>
                        @error('amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Total Amount (calculated, readonly) -->
                    <div class="field-wrapper">
                        <x-ui.label for="total_amount_display">
                            {{ __('Total Amount (RON)') }}
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input
                                type="text"
                                id="total_amount_display"
                                x-bind:value="totalAmount"
                                readonly
                                class="bg-gray-50"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Calculated: (time / 60) × hourly rate') }}</p>
                    </div>
                </div>

                <!-- Custom Fields -->
                @if($customFields->isNotEmpty())
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">
                            {{ __('Custom Fields') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($customFields as $field)
                                <x-tasks.custom-field-input
                                    :field="$field"
                                    :task="$task"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
            <x-ui.button type="button" variant="outline" onclick="window.history.back()">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="primary">
                {{ $task ? __('Update Task') : __('Create Task') }}
            </x-ui.button>
        </div>
    </div>
</form>
