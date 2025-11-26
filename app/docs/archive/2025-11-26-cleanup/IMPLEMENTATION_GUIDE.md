# ClickUp Components - Complete Implementation Guide

## Current Status: Phase 1 Complete ✅

### ✅ COMPLETED:
1. **Status Dropdown** - Unified shared state pattern with multiple triggers
2. **Date Picker** - Full calendar with quick shortcuts and smart date display

---

## Remaining Components - Implementation Code

### 2. TIME TRACKING POPOVER (List View)

**Pattern:** Per-row scoped (inline x-data)

**Replace lines 702-739 in clickup-list.blade.php with:**

```blade
{{-- Time Tracked (ClickUp-style popover) --}}
<div class="px-3 flex-shrink-0" :style="`width: ${columns.time_tracked.width}px`"
     x-data="{
         showTimePopover: false,
         timeForm: {
             input: '',
             description: '',
             billable: true
         },

         parseTime(input) {
             // Parse '3h 20m', '3.5h', '90m', '1h30', etc.
             input = input.toLowerCase().trim();
             let totalMinutes = 0;

             // Match hours and minutes patterns
             const hMatch = input.match(/(\d+\.?\d*)h/);
             const mMatch = input.match(/(\d+)m/);

             if (hMatch) {
                 totalMinutes += parseFloat(hMatch[1]) * 60;
             }
             if (mMatch) {
                 totalMinutes += parseInt(mMatch[1]);
             }

             // If just a number, assume minutes
             if (!hMatch && !mMatch && /^\d+$/.test(input)) {
                 totalMinutes = parseInt(input);
             }

             return Math.round(totalMinutes);
         },

         formatTime(minutes) {
             if (!minutes || minutes === 0) return '–';
             const h = Math.floor(minutes / 60);
             const m = minutes % 60;
             return h > 0 ? `${h}h ${m}m` : `${m}m`;
         },

         async saveTime() {
             const minutes = this.parseTime(this.timeForm.input);

             if (minutes <= 0) {
                 alert('Please enter a valid time (e.g., 3h 20m, 90m, or 1.5h)');
                 return;
             }

             try {
                 const response = await fetch(`/tasks/{{ $task->id }}/time-entries`, {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                     },
                     body: JSON.stringify({
                         minutes: minutes,
                         description: this.timeForm.description,
                         billable: this.timeForm.billable
                     })
                 });

                 const data = await response.json();
                 if (data.success) {
                     // Update displayed time
                     data.time_tracked = (data.time_tracked || 0) + minutes;
                     this.showTimePopover = false;
                     this.timeForm = { input: '', description: '', billable: true };
                     window.location.reload(); // Reload to show updated time
                 } else {
                     alert('Failed to save time entry');
                 }
             } catch (error) {
                 console.error('Error saving time:', error);
                 alert('Failed to save time entry');
             }
         }
     }">

    {{-- Time Display Button --}}
    <button @click="showTimePopover = !showTimePopover"
            class="w-full text-left text-sm px-2 py-1 rounded hover:bg-[#fafafa] transition-colors">
        @php
            $tracked_h = floor($task->time_tracked / 60);
            $tracked_m = $task->time_tracked % 60;
            $tracked_display = $tracked_h > 0 ? "{$tracked_h}h {$tracked_m}m" : ($tracked_m > 0 ? "{$tracked_m}m" : '–');
        @endphp

        <span class="inline-flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            @if($task->time_estimate)
                @php
                    $estimate_h = floor($task->time_estimate / 60);
                    $estimate_m = $task->time_estimate % 60;
                    $estimate_display = $estimate_h > 0 ? "{$estimate_h}h {$estimate_m}m" : "{$estimate_m}m";
                    $variance_pct = $task->time_tracked > 0 ? ($task->time_tracked / $task->time_estimate) * 100 : 0;
                    $color_class = $variance_pct >= 100 ? 'text-red-600' : ($variance_pct >= 80 ? 'text-yellow-600' : 'text-green-600');
                @endphp
                <span class="{{ $color_class }} font-medium">{{ $tracked_display }}</span>
                <span class="text-slate-400"> / {{ $estimate_display }}</span>
            @else
                <span class="text-slate-600">{{ $tracked_display }}</span>
            @endif
        </span>
    </button>

    {{-- Time Tracking Popover --}}
    <div x-show="showTimePopover"
         @click.away="showTimePopover = false"
         x-cloak
         class="absolute right-0 mt-1 w-80 bg-white rounded-lg shadow-xl border border-slate-200 p-4 z-50">

        <h3 class="text-sm font-semibold text-slate-900 mb-3">Add Time Entry</h3>

        {{-- Time Input --}}
        <div class="mb-3">
            <label class="block text-xs font-medium text-slate-700 mb-1">Time</label>
            <input type="text"
                   x-model="timeForm.input"
                   placeholder="e.g., 3h 20m or 90m"
                   class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            <p class="text-xs text-slate-500 mt-1">Format: 3h 20m, 1.5h, or 90m</p>
        </div>

        {{-- Description --}}
        <div class="mb-3">
            <label class="block text-xs font-medium text-slate-700 mb-1">Description (optional)</label>
            <textarea x-model="timeForm.description"
                      rows="2"
                      placeholder="What did you work on?"
                      class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
        </div>

        {{-- Billable Toggle --}}
        <div class="mb-4 flex items-center justify-between">
            <span class="text-xs font-medium text-slate-700">Billable</span>
            <button @click="timeForm.billable = !timeForm.billable"
                    type="button"
                    class="relative inline-flex h-5 w-10 items-center rounded-full transition-colors"
                    :class="timeForm.billable ? 'bg-green-600' : 'bg-slate-300'">
                <span :class="timeForm.billable ? 'translate-x-6' : 'translate-x-1'"
                      class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 pt-3 border-t border-slate-200">
            <button @click="showTimePopover = false"
                    class="flex-1 px-3 py-2 text-sm bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">
                Cancel
            </button>
            <button @click="saveTime()"
                    class="flex-1 px-3 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Save
            </button>
        </div>
    </div>
</div>
```

---

### 3. ENHANCED PRIORITY DROPDOWN

**Find priority dropdown around line 560** and replace with:

```blade
{{-- Priority (enhanced with flag icons) --}}
<div class="px-3 flex-shrink-0" :style="`width: ${columns.priority.width}px`"
     x-data="{ showPriorityDropdown: false }">
    <div class="relative">
        <button @click="showPriorityDropdown = !showPriorityDropdown"
                class="w-full inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-medium hover:shadow-sm transition-all">
            @if($task->priority)
                @php
                    $priorityLabel = strtoupper($task->priority->label);
                    $flagColors = [
                        'URGENT' => 'text-red-600',
                        'HIGH' => 'text-orange-500',
                        'NORMAL' => 'text-blue-500',
                        'LOW' => 'text-slate-400'
                    ];
                    $flagColor = $flagColors[$priorityLabel] ?? 'text-slate-400';
                @endphp
                <svg class="w-4 h-4 {{ $flagColor }}" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                </svg>
            @else
                <svg class="w-4 h-4 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                </svg>
            @endif
        </button>

        {{-- Priority Dropdown --}}
        <div x-show="showPriorityDropdown"
             @click.away="showPriorityDropdown = false"
             x-cloak
             class="absolute left-0 top-full mt-1 w-56 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50">

            {{-- Task Priority Section --}}
            <div class="px-3 py-1 mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Task Priority</span>
            </div>

            {{-- None Option --}}
            <button @click="updatePriorityField(null); showPriorityDropdown = false"
                    class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5 text-slate-500">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                </svg>
                <span>No Priority</span>
            </button>

            <div class="border-t border-slate-200 my-1"></div>

            {{-- Priority Options with Flag Icons --}}
            @foreach($taskPriorities as $priority)
                @php
                    $priorityLabel = strtoupper($priority->label);
                    $priorityConfig = [
                        'URGENT' => ['color' => 'text-red-600', 'bg' => 'hover:bg-red-50'],
                        'HIGH' => ['color' => 'text-orange-500', 'bg' => 'hover:bg-orange-50'],
                        'NORMAL' => ['color' => 'text-blue-500', 'bg' => 'hover:bg-blue-50'],
                        'LOW' => ['color' => 'text-slate-400', 'bg' => 'hover:bg-slate-50']
                    ];
                    $config = $priorityConfig[$priorityLabel] ?? ['color' => 'text-slate-600', 'bg' => 'hover:bg-slate-50'];
                @endphp
                <button @click="updatePriorityField({{ $priority->id }}, '{{ $priority->label }}', '{{ $priority->color }}'); showPriorityDropdown = false"
                        class="w-full px-3 py-1.5 text-left text-sm {{ $config['bg'] }} flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $config['color'] }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                    </svg>
                    <span class="flex-1 {{ $config['color'] }} font-medium">{{ $priority->label }}</span>
                    @if($task->priority_id === $priority->id)
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>
```

---

### 4. ENHANCED ASSIGNEE SELECTOR

**Find assignee selector around line 640** and replace with:

```blade
{{-- Assignee (enhanced with search and sections) --}}
<div class="px-3 flex-shrink-0" :style="`width: ${columns.assignee.width}px`"
     x-data="{
         showAssigneeDropdown: false,
         assignedUsers: @js($task->assignees->pluck('id')->toArray()),
         searchQuery: '',

         get currentUser() {
             return {{ auth()->id() }};
         },

         get filteredUsers() {
             if (!this.searchQuery) return @js($users);
             return @js($users).filter(user =>
                 user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                 user.email.toLowerCase().includes(this.searchQuery.toLowerCase())
             );
         },

         get isCurrentUserAssigned() {
             return this.assignedUsers.includes(this.currentUser);
         },

         async toggleAssignee(userId) {
             const isAssigned = this.assignedUsers.includes(userId);
             const url = `/tasks/{{ $task->id }}/assignees${isAssigned ? `/${userId}` : ''}`;
             const method = isAssigned ? 'DELETE' : 'POST';

             try {
                 const response = await fetch(url, {
                     method: method,
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                     },
                     body: method === 'POST' ? JSON.stringify({ user_id: userId }) : null
                 });

                 const data = await response.json();
                 if (data.success) {
                     this.assignedUsers = data.assignees.map(a => a.id);
                 }
             } catch (error) {
                 console.error('Error updating assignee:', error);
             }
         }
     }">

    {{-- Assignee Display --}}
    <button @click="showAssigneeDropdown = !showAssigneeDropdown"
            class="flex items-center gap-1 hover:bg-slate-50 rounded px-1 -mx-1">
        @if($task->assignees->count() > 0)
            <div class="flex -space-x-2">
                @foreach($task->assignees->take(3) as $assignee)
                    <img src="{{ $assignee->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($assignee->name) }}"
                         alt="{{ $assignee->name }}"
                         class="w-6 h-6 rounded-full border-2 border-white"
                         title="{{ $assignee->name }}">
                @endforeach
                @if($task->assignees->count() > 3)
                    <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-xs font-medium text-slate-600">
                        +{{ $task->assignees->count() - 3 }}
                    </div>
                @endif
            </div>
        @else
            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        @endif
    </button>

    {{-- Assignee Dropdown --}}
    <div x-show="showAssigneeDropdown"
         @click.away="showAssigneeDropdown = false"
         x-cloak
         class="absolute left-0 top-full mt-1 w-64 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50 max-h-96 overflow-y-auto">

        {{-- Search --}}
        <div class="px-3 pb-2">
            <input type="text"
                   x-model="searchQuery"
                   placeholder="Search or enter email..."
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        {{-- Assignees Section --}}
        <div class="px-3 py-1 mb-1">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Assignees</span>
        </div>

        {{-- Me Option --}}
        <button @click="toggleAssignee(currentUser)"
                class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5">
            <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name) }}"
                 alt="Me"
                 class="w-6 h-6 rounded-full">
            <span class="flex-1 font-medium text-slate-900">Me</span>
            <span x-show="isCurrentUserAssigned"
                  class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
        </button>

        <div class="border-t border-slate-200 my-1"></div>

        {{-- People Section --}}
        <div class="px-3 py-1 mb-1">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">People</span>
        </div>

        <template x-for="user in filteredUsers.filter(u => u.id !== currentUser)" :key="user.id">
            <button @click="toggleAssignee(user.id)"
                    class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5">
                <img :src="user.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}`"
                     :alt="user.name"
                     class="w-6 h-6 rounded-full">
                <span class="flex-1 text-slate-700" x-text="user.name"></span>
                <span x-show="assignedUsers.includes(user.id)"
                      class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </button>
        </template>

        <div class="border-t border-slate-200 my-1"></div>

        {{-- Invite via Email --}}
        <button class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5 text-purple-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Invite people via email</span>
        </button>
    </div>
</div>
```

---

### 5. CUSTOM FIELD DROPDOWN (Services Example)

**Find service dropdown around line 468** and enhance it:

```blade
{{-- Service/Custom Field (enhanced with colored pills) --}}
<div class="px-3 flex-shrink-0" :style="`width: ${columns.service.width}px`"
     x-data="{
         showServiceDropdown: false,
         searchQuery: '',

         get filteredServices() {
             if (!this.searchQuery) return @js($services);
             return @js($services).filter(service =>
                 service.name.toLowerCase().includes(this.searchQuery.toLowerCase())
             );
         }
     }">
    <div class="relative">
        <button @click="showServiceDropdown = !showServiceDropdown"
                class="w-full text-left text-sm px-2 py-1 rounded hover:bg-[#fafafa] transition-colors">
            @if($task->service)
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium"
                      style="background-color: {{ $task->service->color }}20; color: {{ $task->service->color }}">
                    {{ $task->service->name }}
                </span>
            @else
                <span class="text-slate-400">Select service...</span>
            @endif
        </button>

        {{-- Service Dropdown --}}
        <div x-show="showServiceDropdown"
             @click.away="showServiceDropdown = false"
             x-cloak
             class="absolute left-0 top-full mt-1 w-64 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50 max-h-80 overflow-y-auto">

            {{-- Search --}}
            <div class="px-3 pb-2">
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Search or add options..."
                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            {{-- None Option --}}
            <button @click="updateServiceField(null); showServiceDropdown = false"
                    class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 text-slate-500">
                None
            </button>

            <div class="border-t border-slate-200 my-1"></div>

            {{-- Service Options as Colored Pills --}}
            <template x-for="service in filteredServices" :key="service.id">
                <button @click="updateServiceField(service.id); showServiceDropdown = false"
                        class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5">
                    {{-- Drag Handle Icon (for visual consistency) --}}
                    <svg class="w-4 h-4 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>

                    {{-- Colored Pill --}}
                    <span class="flex-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                          :style="`background-color: ${service.color}20; color: ${service.color}`"
                          x-text="service.name"></span>

                    {{-- Checkmark if selected --}}
                    <span x-show="{{ $task->service_id }} === service.id"
                          class="w-5 h-5 rounded-full bg-purple-600 flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                </button>
            </template>
        </div>
    </div>
</div>
```

---

## Global Coordination Already Implemented ✅

The date picker and status dropdown already close each other:
- Opening date picker closes status dropdown
- Opening status dropdown closes date picker
- ESC key closes active dropdown
- Click outside closes active dropdown

To extend this pattern to all components, add these closures to per-row dropdowns:
```javascript
@click="
    // Close other shared dropdowns
    closeDatePicker();
    closeStatusMenu();
    // Then toggle this dropdown
    showMyDropdown = !showMyDropdown
"
```

---

## Quick Integration Checklist

1. ✅ Status Dropdown - Complete
2. ✅ Date Picker - Complete
3. ⏳ Time Tracking Popover - Code provided above
4. ⏳ Priority with Flags - Code provided above
5. ⏳ Enhanced Assignee - Code provided above
6. ⏳ Custom Fields - Code provided above

---

## Testing Steps

1. Click time tracked cell → popover opens with time parser
2. Enter "3h 20m" → saves as 200 minutes
3. Click priority → see flag icons with colors
4. Click assignee → see "Me" section + search
5. Click service → see colored pill options
6. Open date picker → all other dropdowns close
7. Press ESC → active dropdown closes
8. Click outside → dropdown closes

---

## API Endpoints Used

All endpoints already exist:
- Time entries: `/tasks/{id}/time-entries` (POST)
- Quick update: `/tasks/{id}/quick-update` (PATCH)
- Assignees: `/tasks/{id}/assignees` (POST/DELETE)

No backend changes needed!

---

Last Updated: 2025-11-24
