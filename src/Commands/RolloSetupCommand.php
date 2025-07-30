<?php

namespace Noxomix\LaravelRollo\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

class RolloSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rollo:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Rollo package with configurations and migrations';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        info('Welcome to Rollo Setup!');
        info('This wizard will help you set up the Rollo package.');

        // Ask for config publishing
        $publishConfig = select(
            label: 'Do you want to publish the Rollo configuration file?',
            options: [
                'publish' => 'Publish configuration',
                'skip' => 'Do not publish',
            ],
            default: 'publish'
        );

        if ($publishConfig === 'publish') {
            info('Publishing Rollo configuration...');
            $this->call('vendor:publish', [
                '--tag' => 'rollo-config',
                '--force' => false,
            ]);
            info('Configuration published successfully!');
        }

        // Ask for migrations and models publishing
        $publishMigrations = select(
            label: 'Do you want to publish Rollo migrations and models?',
            options: [
                'publish' => 'Publish migrations and models',
                'skip' => 'Do not publish',
            ],
            default: 'publish'
        );

        if ($publishMigrations === 'publish') {
            info('Publishing Rollo migrations...');
            $this->call('vendor:publish', [
                '--tag' => 'rollo-migrations',
                '--force' => false,
            ]);
            info('Migrations published successfully!');
            
            // Ask if user wants to run migrations
            if (confirm('Do you want to run the migrations now?', false)) {
                info('Running migrations...');
                $this->call('migrate');
                info('Migrations completed!');
            }
        }

        info('Rollo setup completed!');
        info('You can now start using Rollo in your application.');
    }
}