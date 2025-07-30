<?php

namespace Noxomix\LaravelRollo\Tests\Feature;

use Noxomix\LaravelRollo\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CommandTest extends TestCase
{
    public function test_can_publish_config()
    {
        // Remove existing config first
        $configPath = config_path('rollo.php');
        if (File::exists($configPath)) {
            File::delete($configPath);
        }

        // Test publishing
        Artisan::call('vendor:publish', [
            '--tag' => 'rollo-config',
            '--force' => true,
        ]);

        $this->assertFileExists($configPath);
        
        // Cleanup
        if (File::exists($configPath)) {
            File::delete($configPath);
        }
    }

    public function test_can_publish_migrations()
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'rollo-migrations',
            '--force' => true,
        ]);

        $migrationFiles = File::glob(database_path('migrations/*_create_rollo_table.php'));
        $this->assertNotEmpty($migrationFiles);
        
        // Cleanup
        foreach ($migrationFiles as $file) {
            File::delete($file);
        }
    }

    public function test_can_run_migrations()
    {
        // Run migrations
        Artisan::call('migrate');
        
        // Check if table exists
        $this->assertTrue(
            \Schema::hasTable('rollo_table')
        );
        
        // Rollback
        Artisan::call('migrate:rollback');
    }
}