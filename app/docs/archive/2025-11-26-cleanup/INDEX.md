# Archived Documentation - 2025-11-26

This directory contains documentation files that were archived during a cleanup effort to consolidate project documentation.

**Archive Date:** 2025-11-26
**Reason:** Documentation consolidation - reducing 37 .md files to 7 essential files

## Archived Files

### Duplicates/Superseded Content

| File | Original Purpose | Why Archived |
|------|------------------|--------------|
| `QUICK_REFERENCE.md` | Copy-paste Docker commands | Duplicates content in main README.md |
| `ACTION_PLAN_QUICK_REFERENCE.md` | Security/performance action items | Superseded by consolidated ROADMAP.md |
| `ARCHITECTURE.md` | Form component pattern docs | Short doc, content better in code comments |

### Completed Feature Documentation

| File | Original Purpose | Why Archived |
|------|------------------|--------------|
| `CREDENTIAL_COPY_FEATURE.md` | Copy username/password feature | Feature complete, info is in code |
| `SUBSCRIPTION_FIXES_COMPLETE.md` | Subscription module fixes | Fixes applied, no longer needed |
| `SUBSCRIPTION_FIXES_SUMMARY.md` | Summary of subscription fixes | Duplicate of above |
| `TASK_SAVE_FIX_SUMMARY.md` | Task save functionality fix | Fix complete |
| `WIRE_NOT_DEFINED_FIX.md` | Livewire $wire error fix | Fix applied |

### Livewire Migration Documentation (Migration Complete)

| File | Original Purpose | Why Archived |
|------|------------------|--------------|
| `LIVEWIRE_MIGRATION_COMPLETE.md` | Migration completion status | Migration done |
| `LIVEWIRE_MIGRATION_GUIDE.md` | Step-by-step migration guide | Migration done |
| `LIVEWIRE_QUICK_REFERENCE.md` | Livewire debugging reference | For completed migration |
| `LIVEWIRE_WIRE_VS_THIS.md` | $wire vs @this technical note | Technical detail in code |

### ClickUp Component Documentation (Superseded by Livewire)

| File | Original Purpose | Why Archived |
|------|------------------|--------------|
| `CLICKUP_COMPONENTS.md` | Alpine.js component architecture | Replaced by Livewire components |
| `CLICKUP_COMPONENTS_SUMMARY.md` | Component summary | Superseded |
| `CLICKUP_IMPLEMENTATION_ROADMAP.md` | Feature implementation phases | Partially complete, superseded |
| `IMPLEMENTATION_GUIDE.md` | Component code examples | Old Alpine.js implementation |

### Other Outdated Documentation

| File | Original Purpose | Why Archived |
|------|------------------|--------------|
| `IMPORT_GUIDE.md` | CSV client import instructions | Should be in-app help or user guide |
| `SMARTBILL_IMPORT_OPTIMIZATION.md` | Smartbill integration guide | Incomplete feature, WIP |
| `TASK_VIEW_OPTIMIZATION_ROADMAP.md` | Performance optimization roadmap | Partially implemented |

## Current Documentation Structure

After cleanup, these 7 files remain as essential documentation:

```
/var/www/erp/
├── README.md                        # Infrastructure/Docker setup
├── CHANGELOG.md                     # Version history
├── BACKUP_QUICK_GUIDE.md            # Backup/restore procedures
├── PERMISSIONS_MULTI_USER_SETUP.md  # Permission troubleshooting
├── ERP_AUDIT_REPORT.md              # Security audit (reference)
└── app/
    ├── README.md                    # Application overview
    └── ROADMAP.md                   # Single source of truth
```

## Retrieval

If you need content from archived files:
1. Files remain in this directory for historical reference
2. Content can be extracted if needed for future documentation
3. Technical details have been incorporated into code comments where appropriate

## Notes

- Previous archive exists at: `app/docs/archive/2025-11-14-cleanup/` (12 files)
- Total archived files: 31 (12 + 19)
- Original project had 37+ .md files, now reduced to 7 essential files
