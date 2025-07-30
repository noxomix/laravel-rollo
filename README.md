# Laravel Rollo

## Development Workflow

### Quick Start

```bash
# Initialize package
./scripts/dev-workflow.sh init

# Test in Orchestra Testbench workbench
./scripts/dev-workflow.sh workbench:test

# Test in fresh Laravel installation
./scripts/dev-workflow.sh test:fresh
```

### Available Commands

#### 1. Orchestra Testbench (Recommended for Package Testing)

```bash
# Run tests
composer test

# Start workbench server
composer run serve

# Test artisan commands in isolation
php vendor/bin/testbench artisan rollo:install
php vendor/bin/testbench artisan vendor:publish --tag=rollo-config
```

#### 2. Testing in Fresh Laravel Projects

```bash
# Create and test in new Laravel project
./scripts/test-in-laravel.sh [project-name]

# This will:
# - Create a fresh Laravel installation
# - Install your package from local path
# - Run rollo:install command
# - Test basic functionality
```

#### 3. Development Scripts

```bash
# Show all available commands
./scripts/dev-workflow.sh help

# Initialize package
./scripts/dev-workflow.sh init

# Run package tests
./scripts/dev-workflow.sh test

# Test in fresh Laravel
./scripts/dev-workflow.sh test:fresh

# Start workbench server
./scripts/dev-workflow.sh workbench

# Test in workbench
./scripts/dev-workflow.sh workbench:test

# Test publishing
./scripts/dev-workflow.sh publish:test

# Clean up everything
./scripts/dev-workflow.sh clean
```

### Manual Testing in Existing Laravel Project

```bash
# In your Laravel project's composer.json, add:
"repositories": [
    {
        "type": "path",
        "url": "../laravel-rollo"
    }
]

# Install package
composer require noxomix/laravel-rollo:@dev

# Run install command
php artisan rollo:install --with-migrations

# Update package after changes
composer update noxomix/laravel-rollo
```

### Package Features

- **Install Command**: `php artisan rollo:install`
- **Config Publishing**: `php artisan vendor:publish --tag=rollo-config`
- **Migration Publishing**: `php artisan vendor:publish --tag=rollo-migrations`
- **Facade**: `Rollo::greet('Name')`
- **Routes**: `/rollo`

### Testing Best Practices

1. **Unit/Feature Tests**: Use `composer test` for automated testing
2. **Integration Testing**: Use workbench for testing in Laravel context
3. **Real-world Testing**: Use `test-in-laravel.sh` for fresh installations
4. **Clean Environment**: Use `clean-test.sh` to reset everything