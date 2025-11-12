# ERP Application Architecture Documentation

## Form Component Pattern

This application follows a **DRY (Don't Repeat Yourself)** component architecture for all forms.

### Component Structure

Each module has ONE form component:

1. **`*-form.blade.php`** - Complete form including form tag, CSRF, fields, card, and buttons

### Usage Patterns

#### Dashboard Quick Actions
- **Location:** `/dashboard` main page only
- **Behavior:** Buttons redirect to dedicated create pages
- **Why:** Consistency across the application - no slide panels

#### Module Pages
- **Location:** All module pages (`/clients`, `/domains`, etc.)
- **Uses:** ONLY dedicated pages - NO slide panels
- **Create button:** Redirects to `/module/create`
- **Edit button:** Redirects to `/module/{id}/edit`

### Rules for Future Development

#### DO NOT:
- Add slide panels anywhere in the application
- Duplicate form field definitions
- Create separate field components for forms

#### DO:
- Use dedicated pages for ALL CRUD operations (including dashboard)
- Create a single `*-form.blade.php` component for each module
- Include all form fields directly in the form component
- Use redirect buttons instead of slide panels

### Migration Notes

**November 12, 2025:** Removed all slide panels and consolidated dual-component pattern into single-component pattern.
- Deleted `dashboard/panels.blade.php`
- Deleted all `*-form-fields.blade.php` components
- Consolidated all fields into `*-form.blade.php` components
- Updated dashboard quick actions to redirect to create pages
- Removed all Alpine.js slide panel dispatchers

---

**Last Updated:** November 12, 2025
