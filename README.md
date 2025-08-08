# Laravel Rollo

Context-based, polymorphic role and permission management for Laravel.

## Requirements

- PHP: ^8.2
- Laravel (Illuminate components): ^11.0 | ^12.0

## Installation

```bash
composer require noxomix/laravel-rollo
```

## What It Does

- **Polymorphic**: Any model can have roles and permissions
- **Context-based**: Assignments can be scoped to contexts (tenants, teams, projects)
- **Recursive role inheritance**: Roles can inherit from other roles
- **No auto-enforcement**: You call helpers to build your own RBAC checks

## Getting Started

### 1) Setup (recommended)

```bash
php artisan rollo:setup
```

This publishes `config/rollo.php`, migrations, and can optionally run them.

Alternatively (manual):

```bash
php artisan vendor:publish --tag=rollo-config
php artisan vendor:publish --tag=rollo-migrations
php artisan migrate
```

### 2) Add Traits to Models

```php
use Noxomix\LaravelRollo\Traits\HasRolloRoles;
use Noxomix\LaravelRollo\Traits\HasRolloPermissions;

class User extends Model
{
    use HasRolloRoles, HasRolloPermissions;
}
```

### 3) Add Context Trait (optional)

```php
use Noxomix\LaravelRollo\Traits\AsRolloContext;

class Tenant extends Model
{
    use AsRolloContext;
}
```

## Usage

### Permissions

```php
// Create permission
$permission = RolloPermission::create(['name' => 'edit-posts']);

// Assign to user (kontextfrei)
$user->assignPermission('edit-posts');

// Assign multiple permissions
$user->assignPermissions(['edit-posts', 'delete-posts', 'publish-posts']);

// Assign with context
$tenant = Tenant::find(1);
$context = $tenant->becomeRolloContext();
$user->assignPermission('edit-posts', $context);

// Check permission
$user->hasPermission('edit-posts'); // kontextfrei (context_id = NULL)
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

// Check permission
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

// Note: This package does not auto-enforce permissions; use these helpers in your app logic.
```

## Service API

- `Rollo::has(Model $model, string $permission, mixed $context = null): bool` — checks direct permission and via roles (including inherited roles). If `$context` is provided, only assignments for that `context_id` are considered.
- `Rollo::permissionsFor(Model $model, mixed $context = null): Illuminate\Support\Collection` — returns all effective permissions (direct + via roles), optionally scoped to a context.
- `Rollo::rolesFor(Model $model, mixed $context = null): Illuminate\Support\Collection` — returns all effective roles (direct + inherited), optionally scoped to a context.

## Events

This package dispatches Laravel events for key operations so you can build auditing, logs, or side-effects in your app without coupling it to the package:

- `Noxomix\\LaravelRollo\\Events\\RoleCreated` — payload: `RolloRole $role`
- `Noxomix\\LaravelRollo\\Events\\RoleAssigned` — payload: `Model $model, RolloRole $role`
- `Noxomix\\LaravelRollo\\Events\\RoleRemoved` — payload: `Model $model, RolloRole $role`
- `Noxomix\\LaravelRollo\\Events\\PermissionAssigned` — payload: `Model $model, RolloPermission $permission, ?int $contextId`
- `Noxomix\\LaravelRollo\\Events\\PermissionRemoved` — payload: `Model $model, RolloPermission $permission, ?int $contextId`
- `Noxomix\\LaravelRollo\\Events\\ContextCreated` — payload: `RolloContext $context`
- `Noxomix\\LaravelRollo\\Events\\ContextUpdated` — payload: `RolloContext $context`
- `Noxomix\\LaravelRollo\\Events\\ContextDeleted` — payload: `RolloContext $context`
 - `Noxomix\\LaravelRollo\\Events\\RolesSynced` — payload: `Model $model, array $attached, array $detached, ?int $contextId`
 - `Noxomix\\LaravelRollo\\Events\\PermissionsSynced` — payload: `Model $model, array $attached, array $detached, ?int $contextId`
 - `Noxomix\\LaravelRollo\\Events\\RoleChildAssigned` — payload: `RolloRole $parent, RolloRole $child`
 - `Noxomix\\LaravelRollo\\Events\\RoleChildRemoved` — payload: `RolloRole $parent, RolloRole $child`

Example listener registration:

```php
Event::listen(\Noxomix\LaravelRollo\Events\PermissionAssigned::class, function ($event) {
    // audit($event->model, 'permission_assigned', [
    //     'permission' => $event->permission->name,
    //     'context_id' => $event->contextId,
    // ]);
});
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

// Check via roles and permissions
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

## Configuration

- Allowed Models: The whitelist `config('rollo.allowed_models')` restricts which Eloquent models may be used in dynamic, string-based queries (e.g., context lookups). Package models may be listed when they use the traits (e.g., `Noxomix\\LaravelRollo\\Models\\RolloRole`).
- Config Field Validation: The `config` attribute on roles/permissions is accepted as an array or null. Optional schema-based validation exists in code but is not active by default and carries no required schema; you can ignore it safely for core usage.
 - Context Semantics: When you pass a context to checks or queries, only assignments for that specific `context_id` are considered; kontextfreie assignments (with `context_id = null`) are not included implicitly.

## Architecture

- Context Resolution: Internally, a central resolver (`Noxomix\\LaravelRollo\\Support\\ContextResolver`) resolves a context ID from IDs, `RolloContext`, or arbitrary Eloquent models. Traits and the service class use this resolver to keep behavior consistent and simple.
- Facade Binding: The `Rollo` facade resolves the container singleton bound under the key `rollo`.

## Compatibility

- MySQL-Friendly Pivot: The `rollo_model_has_permissions` table uses a surrogate primary key (`id`) with a unique index over (`permission_id`, `model_type`, `model_id`, `context_id`). This allows `context_id` to remain nullable while staying compatible with databases that do not allow NULL columns in primary keys.

 

## Testing

```bash
composer test
```

## License

MIT
