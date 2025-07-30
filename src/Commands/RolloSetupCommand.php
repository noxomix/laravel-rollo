<?php

namespace Noxomix\LaravelRollo\Commands;

use Illuminate\Console\Command;

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
    public function handle()
    {
        $this->info('Welcome to Rollo Setup!');
        $this->line('This wizard will help you set up the Rollo package.');
        $this->newLine();

        // Ask for config publishing
        $publishConfig = $this->choice(
            'Do you want to publish the Rollo configuration file?',
            ['publish' => 'Publish configuration', 'do not publish' => 'Do not publish'],
            'publish'
        );

        if ($publishConfig === 'publish') {
            $this->info('Publishing Rollo configuration...');
            $this->call('vendor:publish', [
                '--tag' => 'rollo-config',
                '--force' => false,
            ]);
            $this->info('Configuration published successfully!');
        } else {
            $this->line('Skipping configuration publishing.');
        }

        $this->newLine();

        // Ask for migrations and models publishing
        $publishMigrations = $this->choice(
            'Do you want to publish Rollo migrations and models?',
            ['publish' => 'Publish migrations and models', 'do not publish' => 'Do not publish'],
            'publish'
        );

        if ($publishMigrations === 'publish') {
            $this->info('Publishing Rollo migrations...');
            $this->call('vendor:publish', [
                '--tag' => 'rollo-migrations',
                '--force' => false,
            ]);
            $this->info('Migrations published successfully!');
            
            $this->newLine();
            
            // Ask if user wants to run migrations
            if ($this->confirm('Do you want to run the migrations now?', false)) {
                $this->info('Running migrations...');
                $this->call('migrate');
                $this->info('Migrations completed!');
            }
        } else {
            $this->line('Skipping migrations publishing.');
        }

        $this->newLine();
        $this->info('Rollo setup completed!');
        $this->line('You can now start using Rollo in your application.');
        
        return Command::SUCCESS;
    }
}