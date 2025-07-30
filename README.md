# Laravel Rollo

Context-based, polymorphic role and permission management for Laravel.

## Installation

```bash
composer require noxomix/laravel-rollo
```

## Features

- **Polymorphic** - Any model can have roles and permissions
- **Context-based** - All permissions are scoped to contexts (tenants, teams, projects)
- **Recursive role inheritance** - Roles can inherit from other roles
- **No guard_names** - Works without Laravel's guard system
- **Cache optimized** - Built-in caching for permission checks

## Quick Start

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Add Traits to Models

```php
use Noxomix\LaravelRollo\Traits\HasRolloRoles;
use Noxomix\LaravelRollo\Traits\HasRolloPermissions;

class User extends Model
{
    use HasRolloRoles, HasRolloPermissions;
}
```

### 3. Add Context Trait

```php
use Noxomix\LaravelRollo\Traits\RolloHasContext;

class Tenant extends Model
{
    use RolloHasContext;
}
```

## Usage

### Permissions

```php
// Create permission
$permission = RolloPermission::create(['name' => 'edit-posts']);

// Assign to user (global)
$user->assignPermission('edit-posts');

// Assign multiple permissions
$user->assignPermissions(['edit-posts', 'delete-posts', 'publish-posts']);

// Assign with context
$tenant = Tenant::find(1);
$context = $tenant->becomeRolloContext();
$user->assignPermission('edit-posts', $context);

// Check permission
$user->hasPermission('edit-posts'); // global
$user->hasPermission('edit-posts', $context); // in context

// Remove
$user->removePermission('edit-posts');
$user->removePermissions(['edit-posts', 'delete-posts']);
$user->removeAllPermissions(); // remove all
```

### Roles

```php
// Create role
$role = RolloRole::create(['name' => 'editor']);

// Assign permissions to role
$role->assignPermission('edit-posts');

// Assign role to user
$user->assignRole('editor');
$user->assignRole('editor', $context); // with context

// Assign multiple roles
$user->assignRoles(['editor', 'moderator']);

// Check role
$user->hasRole('editor');

// Remove role
$user->removeRole('editor');
$user->removeRoles(['editor', 'moderator']);
```

### Role Inheritance

```php
$adminRole = RolloRole::create(['name' => 'admin']);
$editorRole = RolloRole::create(['name' => 'editor']);

// Admin inherits all editor permissions
$adminRole->assignChildRole($editorRole);
```

### Context Management

```php
// Create context
$context = $tenant->becomeRolloContext(); // auto-creates if not exists
$context = $tenant->becomeRolloContext(['name' => 'EU_Production']); // custom name

// Update context
$tenant->updateRolloContext(); // updates to default name
$tenant->updateRolloContext(['name' => 'New_Name']); // custom name

// Check if has context
if ($tenant->hasRolloContext()) {
    // ...
}

// Delete context
$tenant->deleteRolloContext();
```

### Service Class

```php
use Noxomix\LaravelRollo\Facades\Rollo;

// Check permission (with caching)
if (Rollo::has($user, 'edit-posts')) {
    // ...
}

// Check in context
if (Rollo::has($user, 'edit-posts', $context)) {
    // ...
}

// Get all permissions
$permissions = Rollo::permissionsFor($user);
$permissions = Rollo::permissionsFor($user, $context);

// Get all roles
$roles = Rollo::rolesFor($user);
$roles = Rollo::rolesFor($user, $context);

// Clear cache
Rollo::clearCache();
Rollo::clearCacheFor($user);
```

## Advanced Usage

### JSON Configuration

```php
// Store additional config in roles/permissions
$role = RolloRole::create([
    'name' => 'moderator',
    'config' => [
        'max_posts_per_day' => 10,
        'can_ban_users' => true
    ]
]);

// Access config
$config = $role->getConfig('max_posts_per_day'); // 10
```

### Polymorphic Support

```php
// Any model can have roles/permissions
$team->assignPermission('manage-projects');
$bot->assignRole('data-processor');
$service->assignPermission('api-access');

// Batch operations
$team->assignPermissions(['create-projects', 'edit-projects', 'delete-projects']);
$bot->assignRoles(['data-processor', 'api-consumer']);
```

### Permission Checking

```php
// Direct permission check
if ($user->hasPermission('edit-posts')) {
    // User has direct permission
}

// Check via roles and permissions (uses caching)
if ($user->canPerform('edit-posts')) {
    // User can perform action (direct or via roles)
}

// Check role has permission
if ($role->canPerform('publish-posts')) {
    // Role has this permission
}
```

### Context Helpers

```php
// Get all roles in context
$roles = $tenant->getContextRoles();

// Create role in context
$role = $tenant->createRoleInContext('team-admin', ['order' => 1]);

// Find role in context
$role = $tenant->findRoleInContext('team-admin');

// Get models with permissions in context
$users = $tenant->getModelsWithPermissionsInContext(User::class);
```

## Database Schema

- `rollo_permissions` - Permission definitions
- `rollo_roles` - Role definitions with parent/child relationships
- `rollo_contexts` - Polymorphic contexts (tenants, teams, etc.)
- `rollo_model_has_roles` - Polymorphic role assignments
- `rollo_model_has_permissions` - Polymorphic permission assignments

## Cache

Permission checks are cached for performance. Clear cache when needed:

```bash
php artisan rollo:clear-cache
```

Or programmatically:

```php
Rollo::clearCache();
```

## Testing

```bash
composer test
```

## License

MIT