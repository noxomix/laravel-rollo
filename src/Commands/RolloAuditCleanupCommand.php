<?php

namespace Noxomix\LaravelRollo\Commands;

use Illuminate\Console\Command;
use Noxomix\LaravelRollo\Models\RolloAudit;
use Carbon\Carbon;

class RolloAuditCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rollo:audit-cleanup 
                            {--days= : Number of days to keep (overrides config)}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old Rollo audit logs based on retention settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if audit is enabled
        if (!config('rollo.audit.enabled', false)) {
            $this->warn('Rollo audit logging is disabled. No cleanup needed.');
            return Command::SUCCESS;
        }

        // Get retention days
        $retentionDays = $this->option('days') ?? config('rollo.audit.retention_days');
        
        if (!$retentionDays) {
            $this->info('No retention period configured. Audit logs will be kept forever.');
            return Command::SUCCESS;
        }

        // Calculate cutoff date
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        // Get count of records to delete
        $query = RolloAudit::olderThan($retentionDays);
        $count = $query->count();
        
        if ($count === 0) {
            $this->info('No audit logs older than ' . $retentionDays . ' days found. Nothing to clean up.');
            return Command::SUCCESS;
        }

        // Show what will be deleted
        $this->info("Found {$count} audit log(s) older than {$retentionDays} days (before {$cutoffDate->toDateTimeString()}).");
        
        if ($this->option('dry-run')) {
            $this->table(
                ['Event', 'Created At', 'User', 'Subject'],
                $query->limit(10)->get()->map(function ($audit) {
                    return [
                        $audit->event,
                        $audit->created_at->toDateTimeString(),
                        $audit->user_id ?? 'System',
                        $audit->subject_type ? class_basename($audit->subject_type) . ' #' . $audit->subject_id : '-',
                    ];
                })
            );
            
            if ($count > 10) {
                $this->line('... and ' . ($count - 10) . ' more records.');
            }
            
            $this->info('Dry run completed. No records were deleted.');
            return Command::SUCCESS;
        }

        // Confirm deletion
        if (!$this->option('force')) {
            if (!$this->confirm("Do you want to delete {$count} audit log(s)?")) {
                $this->info('Cleanup cancelled.');
                return Command::SUCCESS;
            }
        }

        // Delete in chunks for better performance
        $deleted = 0;
        $chunkSize = 1000;
        
        $this->withProgressBar($count, function ($progressBar) use ($query, $chunkSize, &$deleted) {
            while ($query->exists()) {
                $deleted += $query->limit($chunkSize)->delete();
                $progressBar->advance($chunkSize);
            }
        });

        $this->newLine(2);
        $this->info("Successfully deleted {$deleted} audit log(s).");
        
        // Show summary of remaining records
        $remaining = RolloAudit::count();
        $this->info("Remaining audit logs: {$remaining}");
        
        return Command::SUCCESS;
    }
}