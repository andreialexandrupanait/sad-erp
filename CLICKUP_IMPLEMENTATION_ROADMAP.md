# ClickUp-Style Task Management - Implementation Roadmap

**Status**: Phase 1 Complete ✅
**Last Updated**: November 23, 2024

---

## ✅ COMPLETED - Phase 1: Multiple Assignees & Watchers

### Database
- ✅ `task_assignees` table with timestamps
- ✅ `task_watchers` table with timestamps
- ✅ Proper foreign keys and indexes
- ✅ Unique constraints

### Backend
- ✅ Task model relationships: `assignees()`, `watchers()`
- ✅ Helper methods: `assignUser()`, `removeAssignee()`, `addWatcher()`, `removeWatcher()`
- ✅ Auto-add assignees as watchers
- ✅ API endpoints for assignee/watcher management
- ✅ Routes registered
- ✅ Eager loading in repository

### Frontend
- ✅ Avatar stack display (up to 3 avatars + overflow indicator)
- ✅ Multi-select dropdown with checkboxes
- ✅ Real-time AJAX add/remove
- ✅ Optimistic UI updates
- ✅ Hover tooltips

---

## 🚧 PHASE 2: Checklists (Week 3-4)

### Database Migrations Needed
```sql
-- Migration 1: task_checklists
CREATE TABLE task_checklists (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    task_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    position INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Migration 2: task_checklist_items
CREATE TABLE task_checklist_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    checklist_id BIGINT NOT NULL,
    text TEXT NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    assigned_to BIGINT NULL,
    position INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (checklist_id) REFERENCES task_checklists(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);
```

### Backend Implementation
**Models** (`/app/Models/`)
- [ ] Create `TaskChecklist.php`
  - Relationships: `task()`, `items()`
  - Scope: `ordered()`
- [ ] Create `TaskChecklistItem.php`
  - Relationships: `checklist()`, `assignedUser()`
  - Accessors: `is_completed` boolean cast
  - Methods: `toggle()`, `complete()`, `uncomplete()`
- [ ] Update `Task.php` model
  - Add relationship: `checklists()`
  - Add helper: `getChecklistProgress()` - returns "3/5 items"

**Controllers** (`/app/Http/Controllers/`)
- [ ] Create `TaskChecklistController.php` or add to TaskController
  ```php
  POST   /tasks/{task}/checklists              - Create checklist
  PATCH  /tasks/{task}/checklists/{id}         - Update checklist name
  DELETE /tasks/{task}/checklists/{id}         - Delete checklist
  POST   /tasks/{task}/checklists/{id}/items   - Add item
  PATCH  /checklist-items/{id}                 - Update item text
  POST   /checklist-items/{id}/toggle          - Toggle completion
  DELETE /checklist-items/{id}                 - Delete item
  PATCH  /checklist-items/reorder              - Reorder items
  ```

**Routes** (`/routes/web.php`)
- [ ] Register all checklist routes

### Frontend Implementation
**Components** (`/resources/views/components/`)
- [ ] Create `tasks/checklist.blade.php`
  - Checklist header with name
  - Progress bar (X/Y completed)
  - Collapse/expand toggle
  - Delete checklist button
- [ ] Create `tasks/checklist-item.blade.php`
  - Checkbox for completion
  - Inline text editing
  - Assignee picker
  - Delete item button
  - Drag handle for reordering

**List View Updates** (`clickup-list.blade.php`)
- [ ] Add checklist progress indicator in name column
  - Show "3/5" badge next to task name
  - Clickable to expand inline preview

**Side Panel Updates** (`tasks/side-panel.blade.php`)
- [ ] Add checklists section
  - "+ Add Checklist" button
  - Inline checklist creation
  - Full checklist editor

**JavaScript/Alpine.js**
- [ ] Checklist component with drag-and-drop (Sortable.js)
- [ ] Inline item creation (Enter to add, Escape to cancel)
- [ ] Optimistic UI for toggle/add/delete
- [ ] Progress calculation

---

## 🚧 PHASE 3: Tags/Labels System (Week 5)

### Database Migrations Needed
```sql
-- Migration 1: task_tags
CREATE TABLE task_tags (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#808080',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(organization_id, name),
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
);

-- Migration 2: task_tag_assignments
CREATE TABLE task_tag_assignments (
    task_id BIGINT NOT NULL,
    tag_id BIGINT NOT NULL,
    PRIMARY KEY(task_id, tag_id),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES task_tags(id) ON DELETE CASCADE
);
```

### Backend Implementation
**Models**
- [ ] Create `TaskTag.php`
  - Scope: `forOrganization()`
  - Methods: color validation
- [ ] Update `Task.php` model
  - Relationship: `tags()`

**Controllers**
- [ ] Create `TaskTagController.php` (in settings)
  ```php
  GET    /settings/task-tags           - List all tags
  POST   /settings/task-tags           - Create tag
  PATCH  /settings/task-tags/{id}      - Update tag
  DELETE /settings/task-tags/{id}      - Delete tag
  ```
- [ ] Update `TaskController.php`
  ```php
  POST   /tasks/{task}/tags            - Add tag
  DELETE /tasks/{task}/tags/{tag}      - Remove tag
  ```

**Routes**
- [ ] Register tag management routes
- [ ] Register task-tag assignment routes

### Frontend Implementation
**Settings Page** (`/resources/views/settings/task-tags/`)
- [ ] `index.blade.php` - Tag management interface
  - List all tags with colors
  - Inline create/edit
  - Color picker
  - Delete confirmation
- [ ] `create.blade.php`
- [ ] `edit.blade.php`

**Components** (`/resources/views/components/`)
- [ ] Create `tasks/tag-selector.blade.php`
  - Multi-select dropdown
  - Autocomplete search
  - Create tag inline
  - Color preview

**List View Updates** (`clickup-list.blade.php`)
- [ ] Add tags column (optional)
- [ ] Show tag badges inline in name column or separate

**Tag Display**
- [ ] Colored pill badges
- [ ] Hover for full tag name
- [ ] Click to filter by tag

**JavaScript/Alpine.js**
- [ ] Tag picker component
- [ ] Autocomplete search
- [ ] Color picker integration

---

## 🚧 PHASE 4: Enhanced Planning Fields (Week 6)

### Database Migration Needed
```sql
-- Add planning fields to tasks table
ALTER TABLE tasks
ADD COLUMN start_date DATE NULL AFTER due_date,
ADD COLUMN time_estimate INT NULL COMMENT 'Estimated time in minutes' AFTER time_tracked,
ADD COLUMN date_closed TIMESTAMP NULL AFTER updated_at;

-- Add index on start_date for queries
ALTER TABLE tasks ADD INDEX idx_start_date (start_date);
```

### Backend Implementation
**Task Model Updates** (`/app/Models/Task.php`)
- [ ] Add to `$fillable`: `start_date`, `time_estimate`, `date_closed`
- [ ] Add to `$casts`: `start_date` => 'date'
- [ ] Add validation: start_date ≤ due_date
- [ ] Add accessor: `getTimeEstimateHoursAttribute()` - format as "2h 30m"
- [ ] Add accessor: `getEstimateVarianceAttribute()` - calculate actual vs estimate
- [ ] Add mutator: Auto-set `date_closed` when status changes to "Done"

**Controller Updates** (`TaskController.php`)
- [ ] Update validation rules for start_date and time_estimate
- [ ] Add date range validation

**Service Updates** (`TaskService.php`)
- [ ] Auto-populate date_closed on status completion
- [ ] Calculate estimate variance

### Frontend Implementation
**Form Updates** (`task-form.blade.php`)
- [ ] Add start_date field with date picker
- [ ] Add time_estimate field with hour/minute input
- [ ] Validation: start_date must be before due_date
- [ ] Visual date range preview

**List View Updates** (`clickup-list.blade.php`)
- [ ] Add start_date column (optional, hidden by default)
- [ ] Update due_date column to show date range if start_date exists
  - Display: "Nov 20 → Nov 25" instead of just "Nov 25"
- [ ] Add time estimate next to time tracked
  - Display: "3h 15m / 2h 30m" (actual/estimate)
  - Red text when overrun
  - Progress bar visual

**Visual Indicators**
- [ ] Color-code tasks by date status:
  - Gray: Not started (today < start_date)
  - Blue: In progress (start_date ≤ today ≤ due_date)
  - Red: Overdue (today > due_date)
- [ ] Time estimate progress bar
  - Green: under estimate
  - Yellow: 80-100% of estimate
  - Red: over estimate

**JavaScript/Alpine.js**
- [ ] Date validation (start ≤ due)
- [ ] Time estimate calculator
- [ ] Visual progress indicators

---

## 🚧 PHASE 5: Task Dependencies (Future)

### Database Migration Needed
```sql
CREATE TABLE task_dependencies (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    task_id BIGINT NOT NULL,
    depends_on_task_id BIGINT NOT NULL,
    dependency_type ENUM('blocks', 'is_blocked_by', 'related') DEFAULT 'blocks',
    created_at TIMESTAMP,
    UNIQUE(task_id, depends_on_task_id),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (depends_on_task_id) REFERENCES tasks(id) ON DELETE CASCADE
);
```

### Implementation Tasks
- [ ] Task model relationships
- [ ] API endpoints (add/remove dependency)
- [ ] Circular dependency validation
- [ ] Visual dependency tree/graph
- [ ] Auto-status updates on dependency completion
- [ ] Dependency warnings in UI

---

## 🚧 PHASE 6: Activity Log/Audit Trail (Future)

### Database Migration
```sql
CREATE TABLE task_activities (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    task_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    action VARCHAR(50) NOT NULL,
    field_changed VARCHAR(100) NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_task_created (task_id, created_at)
);
```

### Implementation Tasks
- [ ] Activity model with polymorphic relationships
- [ ] Event listeners for all task operations
- [ ] Pretty-print field changes
- [ ] Activity timeline component
- [ ] Filter by action type
- [ ] User mentions in activity

---

## 🚧 PHASE 7: Enhanced Time Tracking (Future)

### Database Migration
```sql
CREATE TABLE task_time_entries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    task_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    description TEXT,
    minutes INT NOT NULL,
    billable BOOLEAN DEFAULT TRUE,
    started_at TIMESTAMP,
    ended_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Implementation Tasks
- [ ] Time entry model
- [ ] Start/stop timer widget
- [ ] Time entry CRUD
- [ ] Aggregated time display
- [ ] Billable vs non-billable tracking
- [ ] Time entry list with edit/delete

---

## 🚧 PHASE 8: Advanced Features (Future)

### Recurring Tasks
- [ ] Recurrence pattern storage (JSON)
- [ ] Auto-create task on schedule
- [ ] Recurrence rule editor UI

### Task Templates
- [ ] Template model and storage
- [ ] Template creation from existing task
- [ ] Instantiate task from template
- [ ] Template library UI

### Gantt View
- [ ] Gantt chart component (consider library)
- [ ] Timeline visualization
- [ ] Drag to adjust dates
- [ ] Dependency lines

### Calendar View
- [ ] Calendar component
- [ ] Month/week/day views
- [ ] Drag tasks to reschedule
- [ ] Color by status/priority

---

## 🎨 UX/UI Enhancements (Ongoing)

### Micro-Interactions
- [ ] Smooth drag-and-drop
  - Task reordering within status
  - Task movement between statuses
  - Checklist item reordering
- [ ] Keyboard shortcuts
  - Arrow keys: Navigate tasks
  - Enter: Edit selected task
  - Escape: Cancel/close
  - Ctrl+Enter: Save
  - Delete: Bulk delete
- [ ] Multi-select enhancements
  - Shift+click: Range selection
  - Ctrl+click: Toggle selection
  - Select all checkbox

### Visual Polish
- [ ] Loading states (skeleton screens)
- [ ] Empty states with illustrations
- [ ] Success/error toast notifications
- [ ] Smooth animations (transitions)
- [ ] Hover state refinements
- [ ] Focus indicators for accessibility

---

## 📊 Current Progress Summary

| Phase | Feature | Status | Progress |
|-------|---------|--------|----------|
| 1 | Multiple Assignees & Watchers | ✅ Complete | 100% |
| 2 | Checklists | 📋 Planned | 0% |
| 3 | Tags/Labels | 📋 Planned | 0% |
| 4 | Planning Fields (start_date, estimate) | 📋 Planned | 0% |
| 5 | Task Dependencies | 📋 Future | 0% |
| 6 | Activity Log | 📋 Future | 0% |
| 7 | Enhanced Time Tracking | 📋 Future | 0% |
| 8 | Advanced Features | 📋 Future | 0% |

**Overall ClickUp Feature Parity**: ~70% (with Phases 1-4 complete)

---

## 🎯 Tomorrow's Work Plan

### Priority Order
1. ✅ **Fix 500 error** (timestamps on pivot tables) - DONE
2. **Phase 2: Checklists** - Start with backend
   - Create migrations
   - Create models
   - Create controller endpoints
   - Register routes
3. **Phase 2: Checklists** - Frontend
   - Create checklist component
   - Add to side panel
   - Inline editing
   - Progress indicators
4. **Phase 3: Tags** - If time permits
   - Database setup
   - Basic CRUD
   - Tag picker component

### Files to Create Tomorrow
```
database/migrations/
  - 2024_11_24_create_task_checklists_table.php
  - 2024_11_24_create_task_checklist_items_table.php

app/Models/
  - TaskChecklist.php
  - TaskChecklistItem.php

app/Http/Controllers/
  - TaskChecklistController.php (or extend TaskController)

resources/views/components/tasks/
  - checklist.blade.php
  - checklist-item.blade.php

routes/
  - web.php (add checklist routes)
```

---

## 📝 Notes

- All migrations are reversible with `down()` methods
- Maintain organization-scoped queries throughout
- Use optimistic UI updates for instant feedback
- Keep ClickUp UX patterns (hover states, inline editing, etc.)
- Test on existing tasks to ensure backward compatibility

---

## 🔗 Useful References

- ClickUp List View: https://clickup.com
- Laravel Relationships: https://laravel.com/docs/eloquent-relationships
- Alpine.js: https://alpinejs.dev
- Sortable.js (for drag-drop): https://sortablejs.github.io/Sortable/

---

**Last Updated**: November 23, 2024
**Next Session**: Continue with Phase 2 (Checklists)
