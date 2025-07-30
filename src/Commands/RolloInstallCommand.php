<?php

namespace Noxomix\LaravelRollo\Commands;

use Illuminate\Console\Command;

class RolloInstallCommand extends Command
{
    protected $signature = 'rollo:install
                            {--force : Overwrite existing files}
                            {--with-migrations : Also publish and run migrations}';

    protected $description = 'Install the Laravel Rollo package';

    public function handle()
    {
        $this->info('Installing Laravel Rollo...');

        // Publish config
        $this->callSilent('vendor:publish', [
            '--tag' => 'rollo-config',
            '--force' => $this->option('force'),
        ]);
        $this->info('Published config file');

        // Publish migrations if requested
        if ($this->option('with-migrations')) {
            $this->callSilent('vendor:publish', [
                '--tag' => 'rollo-migrations',
                '--force' => $this->option('force'),
            ]);
            $this->info('Published migrations');

            if ($this->confirm('Would you like to run the migrations now?', true)) {
                $this->call('migrate');
            }
        }

        $this->info('Laravel Rollo installed successfully!');
        $this->line('');
        $this->line('You can now use the Rollo facade:');
        $this->line('  use Noxomix\LaravelRollo\Facades\Rollo;');
        $this->line('  Rollo::greet("World");');
    }
}