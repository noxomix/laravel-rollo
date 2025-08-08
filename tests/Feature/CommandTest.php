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

        $expectedPatterns = [
            '*_create_rollo_permissions_table.php',
            '*_create_rollo_contexts_table.php',
            '*_create_rollo_roles_table.php',
            '*_create_rollo_model_has_roles_table.php',
            '*_create_rollo_model_has_permissions_table.php',
        ];

        foreach ($expectedPatterns as $pattern) {
            $matches = File::glob(database_path('migrations/'.$pattern));
            $this->assertNotEmpty($matches, "Expected migration not published: {$pattern}");
            // Cleanup each matched file
            foreach ($matches as $file) {
                File::delete($file);
            }
        }
    }

    // Note: migration runtime test removed to avoid conflicts with vendor-provided migrations under Testbench.
}
