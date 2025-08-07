# Phase 4: Security Fixes - Plugin Scope Only

*Note: Authorization checks (who can assign/remove permissions) are the responsibility of the implementing application, not this plugin.*

## Critical Security Issues - Plugin Internal Security

### 1. Mass Assignment Vulnerabilities

#### 1.1 Fix RolloContext Mass Assignment
- [ ] Remove `contextable_type` and `contextable_id` from fillable array in `RolloContext.php`
- [ ] Make these fields protected and only settable via constructor or specific methods
- [ ] Add unit tests to verify mass assignment protection

#### 1.2 Review All Models for Mass Assignment
- [ ] Audit `RolloPermission` model fillable fields (only `name`, `config`, `order` should be fillable)
- [ ] Audit `RolloRole` model fillable fields (only `name`, `config`, `order` should be fillable)
- [ ] Remove any system fields from fillable arrays

### 2. Remote Code Execution Prevention

#### 2.1 Fix Uncontrolled Model Instantiation
- [ ] Replace direct instantiation `new $modelClass` in `ModelValidator.php:24`
- [ ] Check against config('rollo.allowed_models') BEFORE instantiation
- [ ] Use `class_exists($class, false)` to prevent autoloading attacks
- [ ] Throw exception for non-whitelisted classes

#### 2.2 Strengthen Class Name Validation
- [ ] Validate class names match pattern: `/^[A-Z][a-zA-Z0-9\\\\]+$/`
- [ ] Ensure class is a subclass of `Illuminate\Database\Eloquent\Model`
- [ ] Prevent instantiation of system classes

### 3. SQL Injection Prevention

#### 3.1 Fix Table Name Validation
- [ ] Update `sanitizeTableName()` regex to: `/^[a-zA-Z0-9_]+$/` (remove dots)
- [ ] Use parameter binding instead of string concatenation in queries
- [ ] Escape table names using `DB::getConnection()->getTablePrefix()`

#### 3.2 Fix Dynamic Queries in RolloContext
- [ ] Fix lines 114-116: Use proper parameter binding for joins
- [ ] Fix lines 139-141: Use query builder instead of raw concatenation
- [ ] Review all uses of `$tableName` variable in queries

### 4. Race Condition Fixes

#### 4.1 Make Role Assignment Atomic
- [ ] Use `INSERT IGNORE` or `ON DUPLICATE KEY UPDATE` for role assignment
- [ ] Add unique constraint: `unique(['model_type', 'model_id', 'role_id'])`
- [ ] Handle duplicate entry exceptions gracefully

#### 4.2 Make Permission Assignment Atomic  
- [ ] Use database transaction for permission assignment
- [ ] Add unique constraint: `unique(['model_type', 'model_id', 'permission_id', 'context_id'])`
- [ ] Return success even if already exists (idempotent)

### 5. Input Validation Hardening

#### 5.1 Strengthen Name Validation
- [ ] Update NAME_PATTERN to: `/^[a-z][a-z0-9-_]{2,254}$/` (start with letter, 3-255 chars)
- [ ] Disallow dots in permission/role names (security risk)
- [ ] Add reserved names check: ['admin', 'root', 'super', 'system']

#### 5.2 Improve Config Validation
- [ ] Expand dangerous keys: `['__proto__', 'constructor', 'prototype', '__defineGetter__', '__defineSetter__', '__lookupGetter__', '__lookupSetter__']`
- [ ] Limit config depth to 5 levels
- [ ] Limit config size to 64KB
- [ ] Validate JSON structure strictly

#### 5.3 Context ID Validation
- [ ] Ensure context IDs are positive integers
- [ ] Verify context exists in database before operations
- [ ] Add foreign key constraints for context_id

### 6. Circular Reference Protection

#### 6.1 Fix Role Inheritance Loop Detection
- [ ] Fix `isInInheritanceChain()` to properly detect cycles
- [ ] Add MAX_INHERITANCE_DEPTH = 10 constant
- [ ] Throw exception when max depth exceeded
- [ ] Add database check constraint if possible

### 7. Error Handling Improvements

#### 7.1 Remove Information Disclosure
- [ ] Replace detailed error messages with generic ones
- [ ] Remove internal paths from error messages
- [ ] Don't expose model class names in errors

#### 7.2 Fix Silent Failures in Batch Operations
- [ ] Remove try-catch that silently continues in `assignPermissions()`
- [ ] Collect all errors and return them
- [ ] Add `strict_mode` config option for fail-fast behavior

### 8. Database Integrity

#### 8.1 Add Missing Database Constraints
- [ ] Migration: Add unique index on `rollo_model_has_roles(model_type, model_id, role_id)`
- [ ] Migration: Add unique index on `rollo_model_has_permissions(model_type, model_id, permission_id, context_id)`
- [ ] Migration: Add foreign key constraints for all relation tables

#### 8.2 Prevent Orphaned Records
- [ ] Add cascade delete for context deletion
- [ ] Add cleanup command for orphaned permissions/roles
- [ ] Add database integrity check command

### 9. Configuration Security

#### 9.1 Secure Default Configuration
- [ ] Remove `RolloRole::class` from default allowed_models (prevent role-to-role assignment)
- [ ] Set restrictive defaults for all config options
- [ ] Validate config array on service provider boot

### 10. Type Safety and Validation

#### 10.1 Add Type Declarations
- [ ] Add strict PHP type declarations to all methods
- [ ] Add return type declarations
- [ ] Use value objects for IDs where possible

#### 10.2 Add Model State Validation
- [ ] Validate model exists before operations
- [ ] Check model is not soft-deleted
- [ ] Validate model type matches expected type

### 11. Testing

#### 11.1 Security Test Suite
- [ ] Test mass assignment protection
- [ ] Test SQL injection prevention
- [ ] Test class instantiation validation
- [ ] Test circular reference detection

#### 11.2 Edge Case Testing
- [ ] Test with very long names (255+ chars)
- [ ] Test with special characters in names
- [ ] Test with null/empty values
- [ ] Test concurrent operations

### 12. Documentation Updates

#### 12.1 Security Documentation
- [ ] Document that authorization is app's responsibility
- [ ] Add security considerations section to README
- [ ] Document safe usage patterns
- [ ] Add migration guide for security updates

## Priority Order

1. **CRITICAL** (Immediate - Prevents RCE/SQLi):
   - Tasks 1.1, 2.1, 2.2, 3.1, 3.2

2. **HIGH** (Next - Prevents data corruption):
   - Tasks 4.1, 4.2, 5.1, 6.1, 8.1

3. **MEDIUM** (Important - Improves security):
   - Tasks 5.2, 5.3, 7.1, 7.2, 9.1

4. **LOW** (Nice to have - Best practices):
   - Tasks 8.2, 10.1, 10.2, 11.1, 11.2, 12.1

## Estimated Time

- Critical fixes: 1-2 days
- High priority: 1-2 days
- Medium priority: 1 day
- Low priority: 1-2 days
- Testing: 1 day

**Total: 5-8 days for security hardening**

## Out of Scope (Application's Responsibility)

The following are NOT the plugin's responsibility:
- Checking if a user can assign/remove permissions
- Checking if a user can create/modify roles
- Checking if a user can manage contexts
- Rate limiting of operations
- Audit logging of who did what
- Session management
- CSRF protection
- API authentication

## Success Criteria

- [ ] No remote code execution possible
- [ ] No SQL injection possible
- [ ] No mass assignment vulnerabilities
- [ ] No race conditions causing data corruption
- [ ] Clean error handling without info disclosure
- [ ] All tests passing
- [ ] Security documentation complete