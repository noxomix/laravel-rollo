# LaravelRollo Package - Implementierungsaufgaben

## Projektübersicht

LaravelRollo ist ein Laravel-Package für kontextbasierte, polymorphe Rollen- und Berechtigungsverwaltung.

### Kernfeatures:
- **Polymorphe Unterstützung**: Beliebige Models (Users, Teams, Services, Bots etc.) können Berechtigungsträger sein
- **Kontextbasiert**: Berechtigungen können kontextgebunden (mit Kontext) oder kontextfrei (ohne Kontext/`NULL`) sein
- **Rekursive Rollenvererbung**: Rollen können andere Rollen referenzieren
- **Minimales Design**: Nur 2 Pivot-Tabellen (model_has_roles, model_has_permissions)
- **Keine Spatie-Dependencies**: Eigenständige Implementierung
- **Keine Guards**: Kein guard_name Konzept
- **Keine hardcodierten User-Beziehungen**: Vollständig generisch

### Namespace: `Noxomix\LaravelRollo`

## Datenbankschema

### 1. rollo_permissions
```sql
- id (bigint, primary key)
- name (string, unique)
- config (json, nullable) -- für zusätzliche Konfiguration
- order (double, nullable) -- für Priorisierung
- created_at (timestamp)
- updated_at (timestamp)
```

### 2. rollo_roles
```sql
- id (bigint, primary key)
- name (string)
- context_id (bigint, nullable, foreign key -> rollo_contexts)
- config (json, nullable) -- für zusätzliche Konfiguration
- order (double, nullable) -- für Priorisierung
- created_at (timestamp)
- updated_at (timestamp)
```

### 3. rollo_contexts
```sql
- id (bigint, primary key)
- name (string, nullable)
- contextable_type (string, nullable) -- Morph-Beziehung
- contextable_id (bigint, nullable) -- Morph-Beziehung
- created_at (timestamp)
- updated_at (timestamp)
```

### 4. rollo_model_has_roles
```sql
- role_id (bigint, foreign key -> rollo_roles)
- model_type (string) -- Polymorphe Beziehung
- model_id (bigint) -- Polymorphe Beziehung
- PRIMARY KEY (role_id, model_type, model_id)
```

### 5. rollo_model_has_permissions
```sql
- permission_id (bigint, foreign key -> rollo_permissions)
- model_type (string) -- Polymorphe Beziehung
- model_id (bigint) -- Polymorphe Beziehung
- context_id (bigint, nullable, foreign key -> rollo_contexts)
- PRIMARY KEY (permission_id, model_type, model_id, context_id)
```

## Kernkonzepte

### Rekursive Rollenvererbung
- Eine Rolle kann über die `rollo_model_has_roles` Tabelle andere Rollen zugewiesen bekommen
- Beispiel: Admin-Rolle bekommt Editor-Rolle zugewiesen → Admin erbt alle Berechtigungen von Editor
- Die Vererbung ist transitiv: A → B → C bedeutet A hat alle Rechte von B und C

### Kontextgebundene und kontextfreie Berechtigungen
- Berechtigungen können mit Kontext (kontextgebunden) oder ohne Kontext (kontextfrei = `context_id NULL`) vergeben werden
- Beispiel: Ein User kann "edit" im Kontext "Team 1" haben, aber nicht in "Team 2"; zusätzlich kann er die Berechtigung kontextfrei haben
- Contexts sind selbst polymorphe Beziehungen (z.B. zu Team, Tenant, Project Models)

## Implementierungsaufgaben

### Phase 1: Package-Grundstruktur

- [ ] **Task 1.1**: Composer.json aktualisieren
  - Namespace bleibt `Noxomix\LaravelRollo`
  - Dependencies nur Laravel Core
  - Keine Spatie-Packages
  
- [ ] **Task 1.2**: Verzeichnisstruktur erweitern
  ```
  src/
    Models/
      RolloPermission.php
      RolloRole.php
      RolloContext.php
    Traits/
      HasRolloRoles.php
      HasRolloPermissions.php
      RolloHasContext.php
    Commands/
      RolloCacheResetCommand.php
    Rollo.php (Service-Klasse)
  database/
    migrations/
      create_rollo_permissions_table.php
      create_rollo_roles_table.php
      create_rollo_contexts_table.php
      create_rollo_model_has_roles_table.php
      create_rollo_model_has_permissions_table.php
  ```

### Phase 2: Migrations

- [ ] **Task 2.1**: Migration für rollo_permissions
  ```php
  Schema::create('rollo_permissions', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
      $table->json('config')->nullable();
      $table->double('order')->nullable();
      $table->timestamps();
  });
  ```

- [ ] **Task 2.2**: Migration für rollo_roles
  ```php
  Schema::create('rollo_roles', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->foreignId('context_id')->nullable()->constrained('rollo_contexts')->onDelete('cascade');
      $table->json('config')->nullable();
      $table->double('order')->nullable();
      $table->timestamps();
      $table->index(['name', 'context_id']);
  });
  ```

- [ ] **Task 2.3**: Migration für rollo_contexts
  ```php
  Schema::create('rollo_contexts', function (Blueprint $table) {
      $table->id();
      $table->string('name')->nullable();
      $table->string('contextable_type')->nullable();
      $table->unsignedBigInteger('contextable_id')->nullable();
      $table->timestamps();
      $table->index(['contextable_type', 'contextable_id']);
  });
  ```

- [ ] **Task 2.4**: Migration für rollo_model_has_roles
  ```php
  Schema::create('rollo_model_has_roles', function (Blueprint $table) {
      $table->foreignId('role_id')->constrained('rollo_roles')->onDelete('cascade');
      $table->string('model_type');
      $table->unsignedBigInteger('model_id');
      $table->primary(['role_id', 'model_type', 'model_id']);
      $table->index(['model_type', 'model_id']);
  });
  ```

- [ ] **Task 2.5**: Migration für rollo_model_has_permissions
  ```php
  Schema::create('rollo_model_has_permissions', function (Blueprint $table) {
      $table->foreignId('permission_id')->constrained('rollo_permissions')->onDelete('cascade');
      $table->string('model_type');
      $table->unsignedBigInteger('model_id');
      $table->foreignId('context_id')->nullable()->constrained('rollo_contexts')->onDelete('cascade');
      $table->primary(['permission_id', 'model_type', 'model_id', 'context_id'], 'rollo_model_has_permissions_primary');
      $table->index(['model_type', 'model_id']);
  });
  ```

### Phase 3: Models

- [ ] **Task 3.1**: RolloPermission Model
  - Properties: id, name, config, order
  - Relationships: models (morphedByMany)
  
- [ ] **Task 3.2**: RolloRole Model
  - Properties: id, name, context_id, config, order
  - Relationships: 
    - context (belongsTo RolloContext)
    - permissions (morphedByMany RolloPermission)
    - models (morphedByMany für alle Models)
    - childRoles (morphMany zu sich selbst über model_has_roles)
    
- [ ] **Task 3.3**: RolloContext Model
  - Properties: id, name, contextable_type, contextable_id
  - Relationships:
    - contextable (morphTo)
    - roles (hasMany RolloRole)

### Phase 4: Traits

- [ ] **Task 4.1**: HasRolloRoles Trait
  ```php
  trait HasRolloRoles {
      public function roles()
      public function assignRole($role, $context = null)
      public function removeRole($role, $context = null)
      public function hasRole($role, $context = null)
      public function getRoleNames($context = null)
  }
  ```

- [ ] **Task 4.2**: HasRolloPermissions Trait
  ```php
  trait HasRolloPermissions {
      public function permissions()
      public function givePermissionTo($permission, $context = null)
      public function revokePermissionTo($permission, $context = null)
      public function hasPermissionTo($permission, $context = null)
      public function getPermissionNames($context = null)
  }
  ```

- [ ] **Task 4.3**: RolloHasContext Trait
  ```php
  trait RolloHasContext {
      public function rolloContext()
      public function createRolloContext($name = null)
      public function getRolloContext()
  }
  ```

### Phase 5: Service-Klasse

- [ ] **Task 5.1**: Rollo Service implementieren
  ```php
  class Rollo {
      // Hauptmethode: Prüft ob Model die Permission hat (direkt oder über Rollen)
      public function has($model, $permissionName, $context = null): bool
      
      // Gibt alle effektiven Permissions zurück (direkt + über Rollen)
      public function permissionsFor($model, $context = null): Collection
      
      // Gibt alle effektiven Rollen zurück (direkt + vererbt)
      public function rolesFor($model, $context = null): Collection
      
      // Private: Rekursive Rollenauflösung
      private function resolveRoleHierarchy($role, &$resolved = []): array
  }
  ```

- [ ] **Task 5.2**: Rekursive Rollenvererbung implementieren
  - Rollen können über model_has_roles andere Rollen zugewiesen bekommen
  - Zirkuläre Referenzen müssen erkannt und verhindert werden
  - Alle transitiven Berechtigungen müssen aufgelöst werden

### Phase 6: Integration

- [ ] **Task 6.1**: Service Provider aktualisieren
  - Migrations publishen
  - Config publishen
  - Singleton für Rollo Service registrieren
  - Commands registrieren

- [ ] **Task 6.2**: Config-Datei erstellen
  ```php
  return [
      'cache' => [
          'enabled' => true,
          'key' => 'rollo.permissions',
          'ttl' => 3600,
      ],
  ];
  ```

- [ ] **Task 6.3**: Artisan Command implementieren
  ```bash
  php artisan rollo:cache-reset
  ```

### Phase 7: Testing

- [ ] **Task 7.1**: Test-Setup
  - TestCase mit Datenbank-Migrations
  - Test-Models erstellen (User, Team, Project)

- [ ] **Task 7.2**: Unit Tests
  - Model-Tests
  - Trait-Tests
  - Service-Tests

- [ ] **Task 7.3**: Feature Tests
  - Rekursive Rollenvererbung
  - Kontextbasierte Berechtigungen
  - Polymorphe Beziehungen

## API-Referenz

### Rollo Service

```php
// Prüft ob $model die Permission $permissionName im $context hat
Rollo::has($model, 'edit-posts', $context);

// Alle Permissions für $model im $context
Rollo::permissionsFor($model, $context);

// Alle Rollen für $model im $context (inkl. vererbte)
Rollo::rolesFor($model, $context);
```

### HasRolloRoles Trait

```php
// Rolle zuweisen
$user->assignRole('admin', $teamContext);

// Rolle entfernen
$user->removeRole('admin', $teamContext);

// Rolle prüfen
$user->hasRole('admin', $teamContext);

// Alle Rollennamen
$user->getRoleNames($teamContext);
```

### HasRolloPermissions Trait

```php
// Permission direkt vergeben
$user->givePermissionTo('edit-posts', $teamContext);

// Permission entziehen
$user->revokePermissionTo('edit-posts', $teamContext);

// Permission prüfen (nur direkte, nicht über Rollen)
$user->hasPermissionTo('edit-posts', $teamContext);

// Alle direkten Permissions
$user->getPermissionNames($teamContext);
```

### RolloHasContext Trait

```php
// Context erstellen
$team->createRolloContext('Team Context');

// Context abrufen
$context = $team->getRolloContext();
```

## Wichtige Hinweise

1. **Keine Spatie-Permissions**: Komplett eigenständige Implementierung
2. **Keine Guards**: Kein guard_name Feld oder Konzept
3. **Polymorphie überall**: Alle Models können Rollen/Permissions haben
4. **Context-First**: Kontext-Checks berücksichtigen nur den angegebenen Kontext; Berechtigungen können zusätzlich kontextfrei (`context_id NULL`) vergeben werden
5. **Performance**: Cache-Layer für häufige Abfragen vorsehen

## Abschluss

Nach Implementierung aller Aufgaben haben wir ein vollständiges, getestetes Laravel-Package für kontextbasierte, polymorphe Berechtigungsverwaltung mit rekursiver Rollenvererbung.
