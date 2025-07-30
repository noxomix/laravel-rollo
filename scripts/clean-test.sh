#!/bin/bash

# Clean up test installations and reset package
# Usage: ./scripts/clean-test.sh

set -e

echo "🧹 Cleaning up test installations..."

# Remove test projects
if [ -d "test-projects" ]; then
    echo "🗑️  Removing test projects..."
    rm -rf test-projects
fi

# Remove vendor and lock file
if [ -d "vendor" ]; then
    echo "🗑️  Removing vendor directory..."
    rm -rf vendor
fi

if [ -f "composer.lock" ]; then
    echo "🗑️  Removing composer.lock..."
    rm -f composer.lock
fi

# Remove workbench build artifacts
if [ -d "workbench/bootstrap/cache" ]; then
    echo "🗑️  Cleaning workbench cache..."
    rm -rf workbench/bootstrap/cache/*
fi

if [ -f "workbench/database/database.sqlite" ]; then
    echo "🗑️  Removing workbench database..."
    rm -f workbench/database/database.sqlite
fi

echo "✅ Cleanup complete!"