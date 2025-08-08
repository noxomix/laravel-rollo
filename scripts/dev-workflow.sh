#!/bin/bash

# Development workflow helper
# Usage: ./scripts/dev-workflow.sh [command]

set -e

COMMAND=${1:-help}

case $COMMAND in
    "init")
        echo "Initializing package development..."
        composer install
        echo "Package initialized!"
        ;;
        
    "test")
        echo "Running package tests..."
        composer test
        ;;
        
    "test:fresh")
        echo "Testing in fresh Laravel installation..."
        ./scripts/test-in-laravel.sh
        ;;
        
    "workbench")
        echo "Starting Testbench workbench..."
        composer install
        composer run build
        composer run serve
        ;;
        
    "workbench:test")
        echo "Testing in workbench..."
        composer install
        composer run build
        php vendor/bin/testbench artisan rollo:setup
        php vendor/bin/testbench artisan migrate
        ;;
        
    "publish:test")
        echo "Testing publish commands..."
        php vendor/bin/testbench artisan vendor:publish --tag=rollo-config
        php vendor/bin/testbench artisan vendor:publish --tag=rollo-migrations
        echo "Publishing tested!"
        ;;
        
    "clean")
        echo "Cleaning up..."
        ./scripts/clean-test.sh
        ;;
        
    "help"|*)
        echo "Laravel Rollo Development Workflow"
        echo ""
        echo "Available commands:"
        echo "  init           - Initialize package (composer install)"
        echo "  test           - Run package tests"
        echo "  test:fresh     - Test in fresh Laravel installation"
        echo "  workbench      - Start Testbench workbench server"
        echo "  workbench:test - Test commands in workbench"
        echo "  publish:test   - Test publish commands"
        echo "  clean          - Clean up test installations"
        echo "  help           - Show this help"
        echo ""
        echo "Example workflow:"
        echo "  ./scripts/dev-workflow.sh init"
        echo "  ./scripts/dev-workflow.sh workbench:test"
        echo "  ./scripts/dev-workflow.sh test:fresh"
        ;;
esac

