# Pull Request

## Description

<!-- Provide a clear and concise description of your change -->

**Issue Reference:** Fixes #(issue number)

**Type of Change:**
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update
- [ ] Performance improvement
- [ ] Code refactoring
- [ ] Security fix

---

## Changes Made

<!-- List the key changes you made in this PR -->

-
-
-

---

## Testing Performed

**Manual Testing:**
- [ ] I have tested this change locally
- [ ] All existing features continue to work
- [ ] New feature/fix works as expected

**Automated Testing:**
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] All new and existing tests pass locally
- [ ] Test coverage has not decreased

**Test Details:**
```
Describe the tests you performed or added
```

---

## Code Quality

**Code Standards:**
- [ ] My code follows PSR-12 coding standards
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have added type hints to all methods
- [ ] I have used meaningful variable and method names

**Laravel Best Practices:**
- [ ] I have used Eloquent ORM (no raw SQL without parameters)
- [ ] I have used Form Requests for validation
- [ ] I have implemented authorization (policies/gates)
- [ ] I have avoided N+1 queries (used eager loading)
- [ ] I have used Laravel helpers where appropriate

---

## Security Checklist

- [ ] I have not introduced any security vulnerabilities
- [ ] Input is properly validated and sanitized
- [ ] Authorization checks are in place
- [ ] No sensitive data is exposed (passwords, API keys, etc.)
- [ ] SQL injection is prevented (parameterized queries)
- [ ] XSS is prevented (proper escaping)
- [ ] CSRF protection is maintained
- [ ] No hardcoded credentials or secrets

---

## Database Changes

**Migrations:**
- [ ] N/A - No database changes
- [ ] I have created migration(s) for schema changes
- [ ] Migration is reversible (down() method implemented)
- [ ] Migration has been tested (migrate + rollback)
- [ ] Indexes are added for foreign keys and frequently queried columns

**Migration Files:**
<!-- List migration files if applicable -->
-

**Database Impact:**
- [ ] No impact on existing data
- [ ] Data migration required (script provided)
- [ ] Potential data loss (documented and approved)

---

## Documentation

- [ ] I have updated the README.md (if applicable)
- [ ] I have updated relevant documentation
- [ ] I have added docblocks for new methods/classes
- [ ] I have updated the CHANGELOG.md
- [ ] API documentation is updated (if applicable)

---

## Deployment Notes

**Prerequisites:**
<!-- List any prerequisites for deploying this change -->
-

**Deployment Steps:**
<!-- Describe special deployment steps if needed -->
1.
2.

**Configuration Changes:**
<!-- List any .env or config changes required -->
-

**Post-Deployment Tasks:**
<!-- List tasks to perform after deployment -->
- [ ] Run `php artisan migrate`
- [ ] Run `php artisan cache:clear`
- [ ] Run `php artisan config:cache`
- [ ] Other:

---

## Breaking Changes

<!-- If this is a breaking change, describe the impact and migration path -->

**Impact:**


**Migration Path for Users:**


---

## Screenshots

<!-- If applicable, add screenshots to help explain your changes -->

**Before:**


**After:**


---

## Performance Impact

- [ ] No performance impact
- [ ] Performance improvement (describe below)
- [ ] Potential performance impact (describe below and mitigation)

**Details:**


---

## Checklist

**General:**
- [ ] My code builds without errors or warnings
- [ ] I have merged the latest changes from main branch
- [ ] There are no merge conflicts
- [ ] All GitHub Actions checks pass

**Review:**
- [ ] I have reviewed my own code before requesting review
- [ ] I have assigned appropriate reviewers
- [ ] I have added appropriate labels

**Communication:**
- [ ] I have notified relevant team members
- [ ] I have updated project management tools (if applicable)

---

## Additional Notes

<!-- Any additional information that reviewers should know -->


---

## Reviewer Guidelines

**For Reviewers:**
1. Check that all checkboxes are completed
2. Verify code follows Laravel and PSR-12 standards
3. Ensure security best practices are followed
4. Test the changes locally if possible
5. Review test coverage and quality
6. Check for performance implications
7. Verify documentation is updated

**Approval Criteria:**
- All automated tests pass
- Code review approved by at least one team member
- Security checklist completed
- Documentation updated
- No unresolved conversations
