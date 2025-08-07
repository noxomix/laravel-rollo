# Phase 3: Security Fixes and Hardening

## Critical Security Issues - Immediate Action Required

### 1. Mass Assignment Vulnerabilities

#### 1.1 Fix RolloContext Mass Assignment
- [ ] Remove `contextable_type` and `contextable_id` from fillable array in `RolloContext.php`
- [ ] Create setter methods with proper validation for these fields
- [ ] Add unit tests to verify mass assignment protection

#### 1.2 Review All Models for Mass Assignment
- [ ] Audit `RolloPermission` model fillable fields
- [ ] Audit `RolloRole` model fillable fields
- [ ] Ensure only safe fields are mass assignable

### 2. Remote Code Execution Prevention

#### 2.1 Fix Uncontrolled Model Instantiation
- [ ] Replace direct instantiation `new $modelClass` in `ModelValidator.php:24`
- [ ] Implement strict whitelist checking before instantiation
- [ ] Add class_exists check with autoload disabled
- [ ] Validate against config('rollo.allowed_models') before any instantiation

#### 2.2 Sanitize Class Names
- [ ] Create `sanitizeClassName()` method
- [ ] Validate class names against regex pattern
- [ ] Ensure no namespace traversal is possible

### 3. Authorization and Access Control

#### 3.1 Add Permission Checks for Permission Assignment
- [ ] Implement authorization check in `assignPermission()` method
- [ ] Verify user has permission to assign permissions in the context
- [ ] Add `can_assign_permissions` permission type
- [ ] Document required permissions for each operation

#### 3.2 Add Authorization for Role Assignment
- [ ] Implement authorization check in `assignRole()` method
- [ ] Verify user has permission to assign roles in the context
- [ ] Add `can_assign_roles` permission type

#### 3.3 Secure Context Operations
- [ ] Add authorization check in `createRolloContext()` method
- [ ] Add authorization check in `updateRolloContext()` method
- [ ] Add authorization check in `deleteRolloContext()` method
- [ ] Implement `can_manage_contexts` permission

### 4. SQL Injection Prevention

#### 4.1 Fix Table Name Validation
- [ ] Update `sanitizeTableName()` regex to exclude dots
- [ ] Use Laravel's `DB::raw()` sparingly and with proper escaping
- [ ] Replace string concatenation with parameter binding
- [ ] Add integration tests for SQL injection attempts

#### 4.2 Parameterize All Queries
- [ ] Review all raw SQL queries in `RolloContext.php`
- [ ] Convert string concatenations to parameter bindings
- [ ] Use Laravel query builder properly for joins

### 5. Race Condition Fixes

#### 5.1 Make Role Assignment Atomic
- [ ] Wrap role assignment in database transaction
- [ ] Use `firstOrCreate()` or `updateOrCreate()` where appropriate
- [ ] Add unique constraints at database level
- [ ] Implement optimistic locking for concurrent updates

#### 5.2 Make Permission Assignment Atomic
- [ ] Wrap permission assignment in database transaction
- [ ] Handle duplicate key exceptions gracefully
- [ ] Add database-level unique constraints

### 6. Input Validation Hardening

#### 6.1 Strengthen Name Validation
- [ ] Update NAME_PATTERN regex to be more restrictive
- [ ] Disallow special characters that could cause issues
- [ ] Add length limits (min: 3, max: 255)
- [ ] Validate against reserved names list

#### 6.2 Improve Config Validation
- [ ] Expand dangerous keys list in `validateConfig()`
- [ ] Add recursive validation for nested arrays
- [ ] Implement max depth check for config arrays
- [ ] Add size limits for config data

#### 6.3 Add Context ID Validation
- [ ] Validate context IDs are integers
- [ ] Check context exists before operations
- [ ] Validate context belongs to expected model type

### 7. Circular Reference Protection

#### 7.1 Fix Role Inheritance Circular Reference Detection
- [ ] Improve `isInInheritanceChain()` algorithm
- [ ] Add maximum depth limit
- [ ] Implement proper cycle detection
- [ ] Add database constraints to prevent circles

#### 7.2 Add Role Hierarchy Depth Limit
- [ ] Implement MAX_INHERITANCE_DEPTH constant
- [ ] Check depth before adding child roles
- [ ] Add configuration option for max depth

### 8. Error Handling and Information Disclosure

#### 8.1 Sanitize Error Messages
- [ ] Remove sensitive information from exceptions
- [ ] Create generic error messages for production
- [ ] Log detailed errors internally only
- [ ] Implement custom exception classes

#### 8.2 Fix Silent Error Handling
- [ ] Remove try-catch blocks that silently continue
- [ ] Log all caught exceptions
- [ ] Return meaningful error responses
- [ ] Add option to fail fast vs. continue on error

### 9. Database Security

#### 9.1 Add Missing Unique Constraints
- [ ] Create migration for unique constraint on model_has_roles
- [ ] Add unique index for (model_type, model_id, role_id, context_id)
- [ ] Add unique index for (model_type, model_id, permission_id, context_id)
- [ ] Test constraint enforcement

#### 9.2 Add Foreign Key Constraints
- [ ] Add foreign key for context_id references
- [ ] Add cascade delete rules where appropriate
- [ ] Ensure referential integrity

### 10. Configuration Security

#### 10.1 Secure Default Configuration
- [ ] Remove `RolloRole::class` from default allowed_models
- [ ] Add configuration validation on boot
- [ ] Document security implications of each config option
- [ ] Add config option for strict mode

#### 10.2 Add Security Configuration Options
- [ ] Add option to disable batch operations
- [ ] Add option for maximum permissions per user
- [ ] Add option for maximum roles per user
- [ ] Add rate limiting configuration

### 11. Additional Security Measures

#### 11.1 Implement Rate Limiting
- [ ] Add rate limiting for permission assignments
- [ ] Add rate limiting for role assignments
- [ ] Add configurable rate limits
- [ ] Log rate limit violations

#### 11.2 Add Security Middleware
- [ ] Create middleware for permission checks
- [ ] Create middleware for context validation
- [ ] Add CSRF protection for all operations
- [ ] Implement request signing for API usage

#### 11.3 Add Audit Trail (Lightweight)
- [ ] Log permission changes to Laravel log
- [ ] Log role changes to Laravel log
- [ ] Log context changes to Laravel log
- [ ] Make logging configurable

### 12. Testing and Validation

#### 12.1 Security Testing
- [ ] Write tests for mass assignment protection
- [ ] Write tests for SQL injection prevention
- [ ] Write tests for authorization checks
- [ ] Write tests for race conditions

#### 12.2 Integration Testing
- [ ] Test privilege escalation scenarios
- [ ] Test context isolation
- [ ] Test circular reference handling
- [ ] Test error handling

#### 12.3 Documentation
- [ ] Document all security features
- [ ] Create security best practices guide
- [ ] Document required permissions for operations
- [ ] Add security warnings to README

## Priority Order

1. **IMMEDIATE** (Do First - Critical Security):
   - Tasks 1.1, 2.1, 3.1, 3.2, 3.3, 4.1

2. **HIGH** (Do Second - High Risk):
   - Tasks 5.1, 5.2, 6.1, 7.1, 9.1

3. **MEDIUM** (Do Third - Important):
   - Tasks 6.2, 6.3, 8.1, 8.2, 10.1

4. **LOW** (Do Last - Nice to Have):
   - Tasks 11.1, 11.2, 11.3, 12.1, 12.2, 12.3

## Estimated Time

- Critical fixes: 2-3 days
- High priority: 2-3 days  
- Medium priority: 1-2 days
- Low priority: 2-3 days
- Testing & Documentation: 2-3 days

**Total: 9-14 days for complete security hardening**

## Success Criteria

- [ ] All critical vulnerabilities patched
- [ ] No failing security tests
- [ ] Security documentation complete
- [ ] Code review by security expert
- [ ] Penetration testing passed