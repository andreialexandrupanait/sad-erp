# ClickUp-Style Reusable Front-End Components

This document describes the ClickUp-style components implemented in the ERP task management system.

## Overview

All components follow a unified architecture pattern with shared state management and proper positioning. They are built with:
- **Backend:** Laravel (Blade templates)
- **Frontend:** Alpine.js (reactivity & state)
- **Styling:** Tailwind CSS
- **API:** AJAX/fetch for all updates (no page reloads except for status changes)

## Architecture Patterns

### Pattern A: Shared Parent State
Used for components that have multiple triggers accessing the same dropdown.

**Components using this pattern:**
1. Status Dropdown ✅ (fully implemented)
2. Date Picker ✅ (fully implemented)

**Key Features:**
- Single dropdown instance for all tasks
- State managed at `clickupListView()` parent level
- Multiple triggers → one menu
- Dynamic positioning via `anchorElement`
- Only one dropdown open at a time

### Pattern B: Per-Row Scoped State
Used for independent row operations.

**Components using this pattern:**
1. Priority Dropdown ✅ (exists, needs visual enhancement)
2. Assignee Selector ✅ (exists, needs visual enhancement)
3. Time Tracking Popover (to be implemented)
4. Custom Field Dropdown (to be implemented)

---

## 1. Status Dropdown ✅ COMPLETE

### Usage
Click either the status icon (left of task name) or the status pill in the Status column.

### Features
- Search functionality
- Status icons with colors
- Checkmark on selected status
- Visual status categories
- Keyboard navigation (ESC to close)

### Code Example
```blade
{{-- Status Icon Trigger --}}
<button @click="openStatusMenu({{ $task->id }}, $event)"
        class="w-5 h-5 rounded-full flex items-center justify-center">
    {{-- Icon SVG --}}
</button>

{{-- Status Column Trigger --}}
<button @click="openStatusMenu({{ $task->id }}, $event)"
        class="w-full inline-flex items-center gap-2">
    <span>{{ $status->label }}</span>
</button>
```

### State Management
```javascript
statusDropdown: {
    isOpen: false,
    taskId: null,
    anchorElement: null
}
```

### Location
- State & Methods: `clickupListView()` in `clickup-list.blade.php` (lines 937-983)
- Dropdown Markup: Lines 836-917
- Triggers: Lines 294, 612

---

## 2. Date Picker ✅ COMPLETE

### Usage
Click the due date cell in any task row.

### Features
- **Left Panel:** Quick shortcuts
  - Today
  - Tomorrow
  - Next week
  - This weekend
  - 2 weeks
  - 4 weeks
  - Clear button

- **Right Panel:** Full month calendar
  - Month navigation (prev/next)
  - "Today" quick button
  - Visual indication of:
    - Today (purple ring)
    - Selected date (purple fill)
  - Current selection display at bottom

- **Smart Date Display:**
  - Past dates: Red text
  - Today: Purple text + "Today" label
  - Tomorrow: "Tomorrow" label
  - Other: "Month Day" format
  - Range support: Shows "start → end" if both dates exist

### Code Example
```blade
{{-- Due Date Cell --}}
<button @click="openDatePicker({{ $task->id }}, $event, 'due',
               '{{ $task->start_date?->format('Y-m-d') }}',
               '{{ $task->due_date?->format('Y-m-d') }}')"
        class="w-full text-left text-sm px-2 py-1 rounded hover:bg-[#fafafa]">
    @if($task->due_date)
        <span class="inline-flex items-center gap-1.5">
            <svg>{{-- Calendar icon --}}</svg>
            @if($isToday)
                <span class="font-medium">Today</span>
            @elseif($isTomorrow)
                <span>Tomorrow</span>
            @else
                <span>{{ $task->due_date->format('M d') }}</span>
            @endif
        </span>
    @else
        <span class="text-slate-400">Add due date</span>
    @endif
</button>
```

### State Management
```javascript
datePicker: {
    isOpen: false,
    taskId: null,
    anchorElement: null,
    mode: 'due', // 'due' or 'start'
    selectedDate: null,
    currentMonth: new Date().getMonth(),
    currentYear: new Date().getFullYear(),
    tempStartDate: null,
    tempDueDate: null
}
```

### Methods
```javascript
// Open date picker
openDatePicker(taskId, event, mode = 'due', startDate = null, dueDate = null)

// Navigation
previousMonth()
nextMonth()

// Selection
selectDate(day)
setQuickDate(option) // 'today', 'tomorrow', 'next_week', etc.
clearDate()

// Apply change
applyDateChange() // Sends PATCH to /tasks/{id}/quick-update
```

### Location
- State & Methods: `clickupListView()` in `clickup-list.blade.php` (lines 944-1183)
- Dropdown Markup: Lines 919-1055
- Trigger: Lines 507-543

### API Integration
```javascript
// Updates task via existing endpoint
PATCH /tasks/{taskId}/quick-update
Body: { due_date: '2025-11-30' } or { start_date: '2025-11-24' }
```

---

## 3. Priority Dropdown (Existing - Needs Enhancement)

### Current State
Basic priority dropdown exists at lines 540-576 of `clickup-list.blade.php`.

### Location
- Component: Per-row scoped `x-data="{ showPriorityDropdown: false }"`
- Lines: 540-576

### Current Features
- Color dots
- None option
- Updates via `updatePriorityField()`

### Planned Enhancements
1. Add colored flag icons (Urgent=red, High=yellow, Normal=blue, Low=gray)
2. Add "Add to Personal Priorities" section
3. Improve visual hierarchy
4. Better hover states

### Usage Example
```blade
<div x-data="{ showPriorityDropdown: false }">
    <button @click="showPriorityDropdown = !showPriorityDropdown">
        {{-- Priority indicator --}}
    </button>

    <div x-show="showPriorityDropdown" @click.away="showPriorityDropdown = false">
        {{-- Priority options --}}
    </div>
</div>
```

---

## 4. Assignee Selector (Existing - Needs Enhancement)

### Current State
Multi-assignee selector exists at lines 578-681 of `clickup-list.blade.php`.

### Location
- Component: Per-row scoped with inline Alpine.js
- Lines: 578-681

### Current Features
- Multi-select support
- Avatar stack display
- Direct API integration (POST/DELETE `/tasks/{task}/assignees/{user}`)
- Local state management

### Planned Enhancements
1. Add search/filter functionality
2. Add "Me" section at top with highlight
3. Add "Teams" section with member count
4. Add "Invite people via email" option
5. Improve visual styling

### Usage Example
```blade
<div x-data="{
    showAssigneeDropdown: false,
    assignedUsers: @js($task->assignees->pluck('id')->toArray()),
    toggleAssignee(userId) { /* API calls */ }
}">
    {{-- Avatar stack --}}
    <button @click="showAssigneeDropdown = !showAssigneeDropdown">
        {{-- Show assigned users --}}
    </button>

    <div x-show="showAssigneeDropdown">
        {{-- User list with checkboxes --}}
    </div>
</div>
```

---

## 5. Time Tracking Popover (To Be Implemented)

### Planned Features
- Time parser: "3h 20m" → minutes
- User selector (who logged the time)
- Date + from/to time fields
- Notes textarea
- Tags selector
- Billable toggle
- Play/stop timer button

### Design Pattern
Per-row scoped component (Pattern B)

### API Endpoints (Already Exist)
```
POST /tasks/{task}/time-entries
PATCH /tasks/{task}/time-entries/{entry}
DELETE /tasks/{task}/time-entries/{entry}
POST /tasks/{task}/time-entries/start
POST /tasks/{task}/time-entries/{entry}/stop
GET /time-entries/running
```

### Planned Structure
```blade
<div x-data="{
    showTimeTracking: false,
    timeInput: '',
    notes: '',
    billable: true,
    parseTime(input) {
        // Parse '3h 20m' into minutes
    },
    saveTimeEntry() {
        // POST to API
    }
}">
    <button @click="showTimeTracking = !showTimeTracking">
        Add time
    </button>

    <div x-show="showTimeTracking" class="popover">
        <input placeholder="Enter time (ex: 3h 20m)">
        <textarea placeholder="Notes"></textarea>
        <button @click="saveTimeEntry()">Save</button>
    </div>
</div>
```

---

## 6. Custom Field Dropdown (To Be Implemented)

### Planned Features
- Search bar
- Colored pill options
- Drag handles for reordering
- "Add option" capability
- Visual pill-style display

### Design Pattern
Per-row scoped component (Pattern B)

### API Integration
Uses existing `updateServiceField()` pattern

### Planned Structure
```blade
<div x-data="{
    showCustomField: false,
    searchQuery: '',
    options: [
        { name: 'Grafica', color: '#3B82F6' },
        { name: 'Maintenance', color: '#A855F7' },
        // ...
    ]
}">
    <button @click="showCustomField = !showCustomField">
        {{-- Selected option or placeholder --}}
    </button>

    <div x-show="showCustomField">
        <input type="text" x-model="searchQuery" placeholder="Search or add options...">
        <template x-for="option in filteredOptions">
            <button class="pill" :style="`background: ${option.color}20; color: ${option.color}`">
                {{-- Drag handle + option name --}}
            </button>
        </template>
    </div>
</div>
```

---

## Global Dropdown Manager

### Current Implementation
Each dropdown closes others when opened:

```javascript
// Status dropdown
openStatusMenu(taskId, event) {
    this.closeDatePicker(); // Close other dropdowns
    // Open status menu
}

// Date picker
openDatePicker(taskId, event, mode, startDate, dueDate) {
    this.closeStatusMenu(); // Close other dropdowns
    // Open date picker
}
```

### Global Close Triggers
- Click outside (via `@click.away`)
- ESC key (via `@keydown.escape.window`)
- Opening another dropdown

---

## Styling System

### Colors (ClickUp-inspired)
```javascript
Primary: #7B68EE (purple) - focus states, selected items
Success: #10B981 (green)
Danger: #EF4444 (red) - past dates, delete actions
Warning: #F59E0B (amber)
Gray scale: Tailwind slate-*
```

### Typography
```css
Base font: 14px / text-sm
Labels: 12px / text-xs
Headers: 12px uppercase / text-xs font-semibold tracking-wide
```

### Component Patterns
```css
Dropdown padding: px-3 py-2
Option height: py-1.5
Border radius: rounded-lg (8px)
Shadow: shadow-xl
Hover: bg-slate-50
Active: bg-purple-50 text-purple-700
Focus ring: ring-2 ring-purple-500
```

---

## Task Row Integration Example

```blade
<div class="flex items-center" x-data="taskRow({{ $task->id }}, ...)">

    {{-- Status Icon --}}
    <button @click="openStatusMenu({{ $task->id }}, $event)">
        {{-- Status icon --}}
    </button>

    {{-- Task Name --}}
    <div class="flex-1">
        <span x-text="data.name"></span>
    </div>

    {{-- Due Date --}}
    <button @click="openDatePicker({{ $task->id }}, $event, 'due', ...)">
        {{ $task->due_date ? $task->due_date->format('M d') : 'Add date' }}
    </button>

    {{-- Status --}}
    <button @click="openStatusMenu({{ $task->id }}, $event)">
        {{ $status->label }}
    </button>

    {{-- Priority --}}
    <div x-data="{ showPriorityDropdown: false }">
        <button @click="showPriorityDropdown = !showPriorityDropdown">
            {{-- Priority --}}
        </button>
    </div>

    {{-- Assignee --}}
    <div x-data="{ showAssigneeDropdown: false, ... }">
        <button @click="showAssigneeDropdown = !showAssigneeDropdown">
            {{-- Avatar stack --}}
        </button>
    </div>

</div>
```

---

## Testing Checklist

- [x] Only one shared dropdown open at a time (Status & Date Picker)
- [x] ESC closes active dropdown
- [x] Click outside closes dropdown
- [x] Multiple triggers work for shared dropdowns
- [x] Date picker calendar navigation works
- [x] Quick date shortcuts work
- [x] Date updates via API
- [ ] All per-row dropdowns work independently
- [ ] Mobile responsive design
- [ ] Keyboard navigation (tab, enter, arrows)
- [ ] Performance optimization

---

## API Endpoints Summary

All necessary endpoints exist:

```
# Task Updates
PATCH /tasks/{task}/quick-update
Body: { due_date, start_date, status_id, priority_id, assigned_to, etc. }

# Assignees
POST /tasks/{task}/assignees
Body: { user_id }

DELETE /tasks/{task}/assignees/{user}

# Time Entries
POST /tasks/{task}/time-entries
PATCH /tasks/{task}/time-entries/{entry}
DELETE /tasks/{task}/time-entries/{entry}
POST /tasks/{task}/time-entries/start
POST /tasks/{task}/time-entries/{entry}/stop
GET /time-entries/running

# Bulk Operations
POST /tasks/bulk-update
Body: { task_ids: [], action, ...data }
```

---

## Implementation Status

### Phase 1: Date Picker ✅ COMPLETE
- [x] Shared state and methods
- [x] Two-column layout (shortcuts + calendar)
- [x] Month navigation
- [x] Quick shortcuts
- [x] API integration
- [x] Smart date display (Today, Tomorrow, past dates in red)
- [x] Trigger from due date cell

### Phase 2: Time Tracking ⏳ IN PROGRESS
- [ ] Time tracking popover component
- [ ] Time parser utility
- [ ] Timer state management
- [ ] API integration

### Phase 3: Enhanced Components
- [ ] Enhance assignee selector
- [ ] Enhance priority dropdown with flags
- [ ] Add search/filter to assignee
- [ ] Add sections (Me, People, Teams)

### Phase 4: Custom Fields
- [ ] Custom field dropdown
- [ ] Colored pill rendering
- [ ] Search/filter
- [ ] Service field integration

### Phase 5: Global Management
- [ ] Global dropdown state
- [ ] Unified close handlers
- [ ] ESC key listener
- [ ] Click-outside detection

---

## Development Guidelines

### Adding a New Dropdown Component

1. **Decide on pattern:**
   - Shared state? → Add to `clickupListView()` parent
   - Per-row? → Use inline `x-data`

2. **For shared state dropdowns:**
   ```javascript
   // In clickupListView()
   myDropdown: {
       isOpen: false,
       taskId: null,
       anchorElement: null
   },

   openMyDropdown(taskId, event) {
       // Close other dropdowns
       this.closeOtherDropdowns();
       // Open this one
       this.myDropdown.isOpen = true;
       this.myDropdown.taskId = taskId;
       this.myDropdown.anchorElement = event.currentTarget;
   }
   ```

3. **Add dropdown markup:**
   ```blade
   <div x-show="myDropdown.isOpen"
        @click.away="closeMyDropdown()"
        @keydown.escape.window="closeMyDropdown()"
        :style="anchorPositioning">
       {{-- Dropdown content --}}
   </div>
   ```

4. **Add trigger:**
   ```blade
   <button @click="openMyDropdown({{ $task->id }}, $event)">
       Trigger
   </button>
   ```

### Best Practices

1. **Always close other dropdowns** when opening a new one
2. **Use consistent styling** (slate colors, rounded-lg, shadow-xl)
3. **Implement keyboard shortcuts** (ESC, Enter, Tab)
4. **Add proper ARIA attributes** for accessibility
5. **Test on mobile** - ensure touch-friendly targets
6. **Optimistic UI updates** - update immediately, rollback on error
7. **Loading states** - show feedback during API calls

---

## Support & Documentation

For questions or issues:
1. Review this documentation
2. Check existing components for reference patterns
3. Consult `/var/www/erp/app/resources/views/components/tasks/clickup-list.blade.php`
4. Test in development environment first

Last Updated: 2025-11-24
