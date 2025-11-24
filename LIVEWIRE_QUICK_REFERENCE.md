# Livewire Task Management - Quick Reference

## ✅ Recent Fixes (2025-11-24)

### Fix #1: $wire is not defined Error
**Problem**: `Uncaught ReferenceError: $wire is not defined` in browser console.

**Root Cause**: Alpine.js was loading BEFORE Livewire, causing `$wire` to be undefined.

**Solution**:
1. Moved Livewire scripts to load BEFORE Alpine.js in `layouts/app.blade.php`
2. Correct order: `@livewireScripts` → Alpine Collapse → Alpine.js
3. Added defensive `typeof $wire !== 'undefined'` checks in task-row.blade.php
4. Fixed task-view.css permissions (chmod 644)

**See**: [WIRE_NOT_DEFINED_FIX.md](WIRE_NOT_DEFINED_FIX.md) for detailed explanation

### Fix #2: Save Functionality Fixed
**Problem**: Changes to task fields weren't persisting to database.

**Solution**:
1. Updated inline edit to use `$wire.call('updateField', field, value)` instead of `$wire.updateField()`
2. Added comprehensive error logging to all TaskRow update methods
3. Added `$this->task->refresh()` after all updates to ensure model is fresh
4. Added console logging in the view for debugging
5. Fixed Alpine.js syntax errors in task-row.blade.php

**How to Debug**:
```bash
# Check Laravel logs for save attempts
docker exec erp_app tail -f /var/www/erp/storage/logs/laravel.log

# Look for these log entries:
# - "TaskRow updateField called" (confirms method is being called)
# - "TaskRow updated successfully" (confirms save worked)
# - "Task update failed" (shows validation or save errors)
```

**Browser Console**:
- Open browser DevTools (F12)
- When editing a task name, you should see: "Blur - saving: [new value]" or "Enter - saving: [new value]"
- Check Network tab for Livewire requests

## 🧪 Testing the Save Functionality

### Test Inline Edit (Task Name)
1. Navigate to `/tasks` in browser
2. Click on any task name to edit it
3. Change the text
4. Press Enter or click away (blur)
5. Check browser console for: "Blur - saving:" or "Enter - saving:"
6. Check Laravel logs for: "TaskRow updateField called"
7. Refresh page - changes should persist

### Test Dropdowns (Status, Priority, Service, List, Assignee)
1. Click on any dropdown field (Status, Priority, etc.)
2. Dropdown should appear
3. Select a new value
4. Check Laravel logs for corresponding update method (e.g., "TaskRow updateStatus called")
5. Refresh page - changes should persist

### Test Date Picker
1. Click on Due Date field
2. Date picker should appear
3. Select a date
4. Check logs for "TaskRow updateDates called"
5. Refresh page - date should persist

## 🚀 Quick Commands

### Clear Caches
```bash
# Using docker exec (recommended)
docker exec erp_app php artisan view:clear
docker exec erp_app php artisan config:clear
docker exec erp_app php artisan route:clear

# Direct PHP (if available)
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

### Check Routes
```bash
php artisan route:list --name=tasks
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

---

## 📁 File Locations

### PHP Components
- `app/Livewire/Tasks/TaskList.php` - Main list
- `app/Livewire/Tasks/TaskRow.php` - Individual row

### Views
- `resources/views/livewire/tasks/task-list.blade.php`
- `resources/views/livewire/tasks/task-row.blade.php`

### Dropdowns
- `resources/views/components/tasks/dropdowns/status.blade.php`
- `resources/views/components/tasks/dropdowns/priority.blade.php`
- `resources/views/components/tasks/dropdowns/assignee.blade.php`
- `resources/views/components/tasks/dropdowns/service.blade.php`
- `resources/views/components/tasks/dropdowns/list.blade.php`
- `resources/views/components/tasks/dropdowns/date-picker.blade.php`

---

## 🔧 Common Modifications

### Add New Field to Task Row

1. **Add to TaskRow.php**:
```php
public function updateNewField($value)
{
    $this->task->update(['new_field' => $value]);
    $this->dispatch('task-updated', taskId: $this->task->id);
}
```

2. **Add to task-row.blade.php**:
```blade
<div class="w-40 px-3">
    <button @click="openNewFieldDropdown($event)">
        {{ $task->new_field ?? 'Not set' }}
    </button>
</div>
```

3. **Add Alpine dispatch method**:
```javascript
openNewField($event) {
    $dispatch('open-newfield-dropdown', {
        taskId: {{ $task->id }},
        anchor: $event.target
    })
}
```

### Add New Dropdown Component

1. **Create** `resources/views/components/tasks/dropdowns/newfield.blade.php`
2. **Copy structure** from `status.blade.php`
3. **Update JavaScript** function names
4. **Add to task-list.blade.php**:
```blade
<x-tasks.dropdowns.newfield />
```

### Change Task Query

Edit `app/Livewire/Tasks/TaskList.php`:
```php
public function getTasksForStatus($statusId)
{
    return Task::where('status_id', $statusId)
        ->when($this->customFilter, fn($q) => $q->where('field', 'value'))
        ->with(['relationships'])
        ->orderBy('column')
        ->limit(100)
        ->get();
}
```

---

## 🎨 Styling Classes

### Container
```
p-6 - Padding
bg-white - Background
rounded-lg - Rounded corners
shadow - Shadow
```

### Table Header
```
h-10 - Height
px-3 - Horizontal padding
bg-gray-50 - Light background
text-xs - Small text
font-medium - Medium weight
```

### Task Row
```
h-9 - Height
border-b - Bottom border
hover:bg-gray-50 - Hover effect
```

---

## 🔍 Debugging

### Enable Livewire Debug Mode
In `config/livewire.php`:
```php
'debug' => env('APP_DEBUG', false),
```

### Check Livewire Updates
Add to browser console:
```javascript
Livewire.hook('message.sent', (message, component) => {
    console.log('Livewire sent:', message);
});
```

### Log Component State
In TaskList.php:
```php
public function render()
{
    \Log::info('TaskList state', [
        'selectedTasks' => $this->selectedTasks,
        'listId' => $this->listId,
    ]);

    return view(...);
}
```

---

## ⚡ Performance Tips

### 1. Eager Load Relationships
```php
->with(['assignees', 'priority', 'service', 'status', 'list'])
```

### 2. Limit Results
```php
->limit(100)
```

### 3. Cache Task Counts
```php
Cache::remember('task-counts', 60, function() {
    return Task::groupBy('status_id')->count();
});
```

### 4. Use Pagination for Large Lists
```php
->paginate(50)
```

---

## 🐛 Common Issues & Fixes

### Issue: "Class not found"
```bash
composer dump-autoload
php artisan clear-compiled
```

### Issue: "View not found"
```bash
php artisan view:clear
```

### Issue: "Property not found"
Make sure property is public in Livewire component:
```php
public $myProperty;
```

### Issue: Dropdown not opening
Check that Alpine.js is loaded:
```javascript
// In browser console
console.log(typeof Alpine);
// Should output: "object"
```

### Issue: Task not updating
Check `$fillable` in Task model:
```php
protected $fillable = [
    'name', 'status_id', 'priority_id', // ... etc
];
```

---

## 📊 Database Queries

### Check Slow Queries
Enable query logging in `AppServiceProvider.php`:
```php
\DB::listen(function($query) {
    if ($query->time > 100) {
        \Log::warning('Slow query', [
            'sql' => $query->sql,
            'time' => $query->time
        ]);
    }
});
```

### Count Tasks by Status
```bash
php artisan tinker
>>> Task::groupBy('status_id')->selectRaw('status_id, count(*) as count')->get()
```

---

## 🎯 Event Reference

### Available Events

**From Task Row**:
- `open-status-dropdown`
- `open-priority-dropdown`
- `open-assignee-dropdown`
- `open-service-dropdown`
- `open-list-dropdown`
- `open-date-picker`

**From Livewire**:
- `task-updated` (with taskId)
- `tasks-updated` (global refresh)
- `task-row-refresh-{id}` (specific row)

### Listening to Events

In Livewire component:
```php
#[On('task-updated')]
public function refreshTask($taskId)
{
    // Handle refresh
}
```

In Alpine:
```blade
<div @task-updated.window="console.log($event.detail)">
```

---

## 🔐 Security

### CSRF Protection
Automatically handled by Livewire - no action needed.

### Authorization
Add to TaskList.php:
```php
public function mount()
{
    $this->authorize('viewAny', Task::class);
}
```

### Input Validation
```php
public function updateField($field, $value)
{
    $this->validate([
        $field => 'required|string|max:255'
    ]);

    $this->task->update([$field => $value]);
}
```

---

## 📦 Useful Livewire Features

### Loading States
```blade
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>
```

### Polling
```blade
<div wire:poll.5s="refreshData">
    <!-- Auto-refresh every 5 seconds -->
</div>
```

### Lazy Loading
```php
#[Lazy]
class TaskList extends Component
```

### URL Parameters
```php
#[Url]
public $search = '';
```

---

This quick reference covers the most common operations and troubleshooting steps for the Livewire task management system.
