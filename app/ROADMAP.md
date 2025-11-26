# SimpleAD ERP - Project Roadmap

> **Single Source of Truth** | Last updated: 2025-11-26

---

## Quick Status

| Area | Status |
|------|--------|
| **Production Ready** | ~70% |
| **Security** | Medium (policies needed) |
| **Performance** | Good (caching implemented) |
| **Test Coverage** | <10% |

---

## Module Status

| Module | Status | Completion | Notes |
|--------|--------|------------|-------|
| Domains | Done | 100% | Multi-tenant, policies pending |
| Subscriptions | Done | 100% | Billing cycles, logging |
| Internal Accounts | Done | 100% | Password encryption |
| Credentials | Done | 100% | Encrypted storage |
| Clients | Done | 100% | Full CRUD, import/export |
| Tasks (Livewire) | Done | 95% | Service-based, inline editing |
| Settings | Done | 100% | Dynamic options via SettingOption |
| Dashboard | Done | 90% | Stats, charts, caching |
| Auth | Done | 90% | Breeze + roles |
| Financial | Done | 95% | Revenue/expense, reports |

---

## Recently Completed (Nov 2025)

- [x] Livewire task management migration (Nov 24)
- [x] Dashboard caching for performance (Nov 26)
- [x] Queue worker for background jobs (Nov 26)
- [x] Subscription billing cycle fixes (Nov 25)
- [x] Multi-user permission system (Nov 26)
- [x] Smartbill integration - partial (Nov 20)
- [x] Documentation cleanup - 37 to 7 files (Nov 26)

---

## In Progress

- [ ] Pending migrations (performance indexes)
- [ ] Smartbill import views (incomplete)

---

## High Priority (Next)

### Security (Critical)
- [ ] Implement authorization policies for all models
- [ ] Complete AuditLogger middleware
- [ ] Add rate limiting to sensitive routes
- [ ] Implement 2FA (Google Authenticator)
- [ ] Remove hardcoded secrets from docker-compose.yml

### Performance
- [ ] Run pending performance migrations
- [ ] Optimize N+1 queries in remaining areas
- [ ] Add Redis for session/cache (optional)

### Testing
- [ ] Write feature tests for core modules
- [ ] Set up CI/CD pipeline (GitHub Actions)
- [ ] Achieve 70% test coverage

---

## Backlog (Medium Priority)

- [ ] Complete Smartbill import views
- [ ] Contracts & Annexes UI
- [ ] Email notifications for renewals/expiries
- [ ] Advanced reporting module
- [ ] REST API for integrations
- [ ] Password rotation policy for Internal Accounts
- [ ] Bulk actions for Domains

---

## Technical Debt

| Issue | Impact | Effort |
|-------|--------|--------|
| No authorization policies | High | 1 week |
| Missing tests | High | 2 weeks |
| Business logic in controllers | Medium | 2 weeks |
| Hardcoded strings (i18n incomplete) | Low | 1 week |
| Empty CheckRole middleware | High | 2 days |

---

## Performance Targets

| Metric | Current | Target |
|--------|---------|--------|
| Dashboard load | ~800ms | <500ms |
| Task list (500 tasks) | ~2s | <500ms |
| Page load (avg) | ~1s | <300ms |
| Memory usage | ~200MB | <100MB |

---

## Security Checklist (from Audit)

### Critical (Fix Before Production)
- [ ] Authorization policies for all models
- [ ] Audit logging for critical operations
- [ ] Rate limiting on auth routes
- [ ] No hardcoded secrets in repo

### High Priority
- [ ] 2FA support
- [ ] Password complexity rules
- [ ] Session timeout configuration

### Medium Priority
- [ ] Encrypt sensitive PII fields
- [ ] Security dependency audit (composer audit)

---

## Maintenance Checklist

### Daily
- [ ] Check queue worker status: `docker ps | grep erp_queue`
- [ ] Monitor error logs: `docker exec erp_app tail -100 storage/logs/laravel.log`

### Weekly
- [ ] Database backup verification
- [ ] Clear old cache: `docker exec erp_app php artisan cache:clear`
- [ ] Review audit logs

### Monthly
- [ ] Security audit: `docker exec erp_app composer audit`
- [ ] Performance review
- [ ] Documentation update

---

## Key Files Reference

| Purpose | Location |
|---------|----------|
| Docker/Infra Setup | `/var/www/erp/README.md` |
| Backup Procedures | `/var/www/erp/BACKUP_QUICK_GUIDE.md` |
| Permission Issues | `/var/www/erp/PERMISSIONS_MULTI_USER_SETUP.md` |
| Change History | `/var/www/erp/CHANGELOG.md` |
| App Overview | `/var/www/erp/app/README.md` |
| Security Audit | `/var/www/erp/ERP_AUDIT_REPORT.md` |

---

## Infrastructure

### Docker Services
- `erp_app` - PHP-FPM (Laravel)
- `erp_web` - Nginx
- `erp_db` - MySQL 8.0
- `erp_queue` - Queue worker

### Ports
- Web: 8085
- MySQL: 3307 (external), 3306 (internal)

### Commands
```bash
# Start all services
docker compose up -d

# Run migrations
docker exec erp_app php artisan migrate

# Clear caches
docker exec erp_app php artisan cache:clear && \
docker exec erp_app php artisan config:clear && \
docker exec erp_app php artisan view:clear

# Check queue worker
docker exec erp_queue ps aux | grep queue:work

# View logs
docker logs -f erp_app
```

---

## Update Log

| Date | Changes |
|------|---------|
| 2025-11-26 | Documentation consolidated (37 to 7 files), queue worker added, dashboard caching |
| 2025-11-25 | Subscription fixes complete, currency support |
| 2025-11-24 | Livewire task migration complete |
| 2025-11-20 | Smartbill integration started |
| 2025-11-14 | Initial documentation cleanup (12 files archived) |
| 2025-11-11 | Settings module refactored |
| 2025-11-09 | Initial ERP implementation |

---

## Archived Documentation

Historical documentation has been archived to:
- `app/docs/archive/2025-11-14-cleanup/` (12 files)
- `app/docs/archive/2025-11-26-cleanup/` (19 files)

See `INDEX.md` in each archive directory for details.
