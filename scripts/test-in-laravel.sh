#!/bin/bash

# Test Laravel Package in fresh Laravel installation
# Usage: ./scripts/test-in-laravel.sh [project-name]

set -e

PROJECT_NAME=${1:-test-app}
PACKAGE_PATH=$(pwd)
TEST_DIR="test-projects"

echo "🚀 Testing Laravel Rollo package in fresh Laravel project: $PROJECT_NAME"
echo "📦 Package path: $PACKAGE_PATH"

# Create test projects directory
mkdir -p $TEST_DIR
cd $TEST_DIR

# Remove existing project if exists
if [ -d "$PROJECT_NAME" ]; then
    echo "🗑️  Removing existing project..."
    rm -rf $PROJECT_NAME
fi

# Create new Laravel project
echo "📥 Creating new Laravel project..."
composer create-project laravel/laravel $PROJECT_NAME --quiet

cd $PROJECT_NAME

# Add local package repository
echo "🔗 Adding local package repository..."
composer config repositories.local path "$PACKAGE_PATH"

# Require the package
echo "📦 Installing package..."
composer require noxomix/laravel-rollo:@dev

# Run install command
echo "⚙️  Running package install command..."
php artisan rollo:install --with-migrations --force

# Run tests
echo "🧪 Testing package functionality..."
php artisan tinker --execute="
use Noxomix\LaravelRollo\Facades\Rollo;
echo Rollo::greet('Test User') . PHP_EOL;
echo 'Enabled: ' . (Rollo::isEnabled() ? 'Yes' : 'No') . PHP_EOL;
"

# Test route
echo "🌐 Testing package routes..."
php artisan route:list | grep rollo || echo "No rollo routes found"

# Show success
echo ""
echo "✅ Package testing complete!"
echo "📁 Test project location: $(pwd)"
echo ""
echo "You can now:"
echo "  cd $TEST_DIR/$PROJECT_NAME"
echo "  php artisan serve"
echo "  Visit: http://localhost:8000/rollo"