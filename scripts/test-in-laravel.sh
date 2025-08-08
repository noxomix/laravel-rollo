#!/bin/bash

# Test Laravel Package in fresh Laravel installation
# Usage: ./scripts/test-in-laravel.sh [project-name]

set -e

PROJECT_NAME=${1:-test-app}
PACKAGE_PATH=$(pwd)
TEST_DIR="test-projects"

echo "Testing Laravel Rollo package in fresh Laravel project: $PROJECT_NAME"
echo "Package path: $PACKAGE_PATH"

# Create test projects directory
mkdir -p "$TEST_DIR"
cd "$TEST_DIR"

# Remove existing project if exists
if [ -d "$PROJECT_NAME" ]; then
    echo "Removing existing project..."
    rm -rf "$PROJECT_NAME"
fi

# Create new Laravel project
echo "Creating new Laravel project..."
composer create-project laravel/laravel "$PROJECT_NAME" --quiet

cd "$PROJECT_NAME"

# Add local package repository
echo "Adding local package repository..."
composer config repositories.local path "$PACKAGE_PATH"

# Require the package
echo "Installing package..."
composer require noxomix/laravel-rollo:@dev

# Run setup command
echo "Running package setup command..."
php artisan rollo:setup

echo "Package setup completed."

echo ""
echo "Package testing complete!"
echo "Test project location: $(pwd)"
echo ""
echo "You can now:"
echo "  cd $TEST_DIR/$PROJECT_NAME"
echo "  php artisan serve"
