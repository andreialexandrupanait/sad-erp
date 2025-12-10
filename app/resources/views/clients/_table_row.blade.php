@php
    $clientStatus = $client->status;
    $statusBg = $clientStatus ? $clientStatus->color_background : '#e2e8f0';
    $statusText = $clientStatus ? $clientStatus->color_text : '#475569';
    $statusName = $clientStatus ? $clientStatus->name : __('No Status');
@endphp
<tr class="border-b transition-colors hover:bg-slate-50/50 client-row"
    data-selectable
    data-client-id="{{ $client->id }}"
    data-client-slug="{{ $client->slug }}"
    data-status-id="{{ $client->status_id ?? 'null' }}">
    <td class="px-6 py-4 align-middle">
        <x-bulk-checkbox
            @change="toggleItem({{ $client->id }})"
            x-bind:checked="selectedIds.includes({{ $client->id }})"
        />
    </td>
    <td class="px-6 py-4 align-middle">
        <div>
            <a href="{{ route('clients.show', $client) }}" class="text-sm font-semibold text-slate-900 hover:text-slate-600 transition-colors">
                {{ $client->name }}
            </a>
            @if($client->contact_person)
                <div class="text-sm text-slate-500">{{ $client->contact_person }}</div>
            @endif
        </div>
    </td>
    <td class="px-6 py-4 align-middle">
        @if($client->email)
            <div class="text-sm text-slate-900 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                 onclick="copyToClipboard('{{ $client->email }}', this)"
                 title="Click to copy">
                <span>{{ $client->email }}</span>
                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
        @else
            <div class="text-sm text-slate-500">—</div>
        @endif
        @if($client->phone)
            <div class="text-sm text-slate-500 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                 onclick="copyToClipboard('{{ $client->phone }}', this)"
                 title="Click to copy">
                <span>{{ $client->phone }}</span>
                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif
    </td>
    <td class="px-6 py-4 align-middle">
        {{-- Status dropdown --}}
        <div x-data="statusDropdown({{ $client->status_id ?? 'null' }}, '{{ $statusBg }}', '{{ $statusText }}', '{{ addslashes($statusName) }}', '{{ $client->slug }}')"
             @click.away="open = false"
             class="relative">
            {{-- Status badge button --}}
            <button type="button"
                    @click="open = !open"
                    :class="{ 'opacity-50 pointer-events-none': saving }"
                    class="cursor-pointer transition-all hover:scale-105 active:scale-95"
                    title="{{ __('Click to change status') }}">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium status-indicator"
                      data-client-slug="{{ $client->slug }}"
                      :style="'background-color: ' + currentBg + '; color: ' + currentText">
                    <span x-show="saving" class="mr-1">
                        <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                    <span x-text="currentName"></span>
                    <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </span>
            </button>

            {{-- Dropdown menu --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute z-50 mt-1 left-0 w-40 bg-white rounded-lg shadow-lg border border-slate-200 py-1"
                 style="display: none;">
                @foreach($clientStatuses ?? [] as $status)
                    <button type="button"
                            @click="updateStatus({{ $status->id }}, '{{ addslashes($status->name) }}', '{{ $status->color_background }}', '{{ $status->color_text }}')"
                            class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2 transition-colors"
                            :class="{ 'bg-slate-100': currentStatusId === {{ $status->id }} }">
                        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $status->color_background }};"></span>
                        <span>{{ $status->name }}</span>
                        <svg x-show="currentStatusId === {{ $status->id }}" class="w-4 h-4 ml-auto text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                @endforeach
            </div>
        </div>
    </td>
    <td class="px-6 py-4 align-middle text-right">
        <div class="text-sm font-semibold text-slate-900">
            {{ number_format($client->total_incomes, 2) }} RON
        </div>
        @if($client->invoices_count > 0)
            <div class="text-xs text-slate-500">{{ $client->invoices_count }} {{ __('invoices') }}</div>
        @endif
    </td>
    <td class="px-6 py-4 align-middle text-right">
        <x-table-actions
            :viewUrl="route('clients.show', $client)"
            :editUrl="route('clients.edit', $client)"
            :deleteAction="route('clients.destroy', $client)"
            :deleteConfirm="__('Are you sure you want to delete this client?')"
        />
    </td>
</tr>
