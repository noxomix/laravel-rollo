# Rollo Audit System - Implementation Tasks

## Overview
Implement a comprehensive audit logging system for Laravel Rollo that tracks all permission and role changes with configurable storage options.

## Phase 1: Foundation (High Priority)

### ✅ 1. Add Audit Configuration
- [x] Add audit section to config/rollo.php
- [x] Support for enable/disable
- [x] Multi-driver configuration (database, log)
- [x] Retention settings
- [x] Metadata options

### 2. Create Migration for rollo_audits Table
- [ ] Create migration file
- [ ] Define table structure:
  - id, event, auditable (morph), subject (morph)
  - old_values, new_values (JSON)
  - ip_address, user_agent
  - user_id (who made the change)
  - metadata (JSON)
  - created_at
- [ ] Add proper indexes for performance

### 3. Create RolloAudit Model
- [ ] Create Model class
- [ ] Define fillable/casts
- [ ] Add query scopes:
  - scopeForUser($userId)
  - scopeForEvent($event)
  - scopeRecent($days)
  - scopeForModel($model)
- [ ] Add relationships (auditable, subject, user)

## Phase 2: Core Service (Medium Priority)

### 4. Create RolloAuditService
- [ ] Create service class
- [ ] Implement log() method
- [ ] Handle multi-driver logging
- [ ] Collect metadata (IP, User-Agent, etc.)
- [ ] Event filtering logic

### 5. Add Audit to HasRolloPermissions Trait
- [ ] Hook into assignPermission()
- [ ] Hook into removePermission()
- [ ] Hook into syncPermissions()
- [ ] Log old and new values

### 6. Add Audit to HasRolloRoles Trait
- [ ] Hook into assignRole()
- [ ] Hook into removeRole()
- [ ] Hook into syncRoles()
- [ ] Log context information

## Phase 3: Extended Coverage (Low Priority)

### 7. Add Audit to Model Operations
- [ ] RolloRole: create, update, delete
- [ ] RolloPermission: create, update, delete
- [ ] RolloContext: create, update, delete
- [ ] Use Laravel model events

### 8. Implement Log Driver Support
- [ ] Format audit data for log files
- [ ] Use configured log channel
- [ ] Structured logging format
- [ ] Handle log rotation

### 9. Create Cleanup Command
- [ ] Create rollo:audit-cleanup command
- [ ] Respect retention_days config
- [ ] Add --force option
- [ ] Show summary of deleted records
- [ ] Schedule-friendly implementation

## Phase 4: API and Helpers

### 10. Audit Query API
- [ ] Add methods to Rollo facade:
  - Rollo::audit()->recent()
  - Rollo::auditFor($model)
  - Rollo::auditByUser($user)
- [ ] Helper methods for common queries

### 11. Testing
- [ ] Unit tests for AuditService
- [ ] Integration tests for trait hooks
- [ ] Test multi-driver functionality
- [ ] Test cleanup command
- [ ] Performance tests

## Phase 5: Documentation

### 12. Documentation
- [ ] Update README with audit feature
- [ ] Configuration examples
- [ ] Usage examples
- [ ] Performance considerations

## Implementation Notes

### Event Types
- `permission.assigned` / `permission.removed` / `permission.synced`
- `role.assigned` / `role.removed` / `role.synced`
- `role.created` / `role.updated` / `role.deleted`
- `permission.created` / `permission.updated` / `permission.deleted`
- `context.created` / `context.updated` / `context.deleted`

### Performance Considerations
- Use database transactions
- Consider queueing for high-traffic apps
- Implement batch inserts where possible
- Index optimization for common queries

### Security Considerations
- Sanitize metadata before storage
- Ensure audit logs cannot be tampered with
- Consider encryption for sensitive data
- Implement access controls for viewing audits