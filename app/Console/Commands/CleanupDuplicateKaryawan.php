<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\HR\Domain\Models\Employee;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateKaryawan extends Command
{
    protected $signature = 'cleanup:duplicate-karyawan {--dry-run : Preview deletions} {--force : Skip confirmation}';
    protected $description = 'Remove duplicate employee records (keeping the oldest one)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info("🔍 Finding duplicate employees...");

        $duplicates = Employee::select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info("✅ No duplicate employees found.");
            return 0;
        }

        $this->info("Found {$duplicates->count()} users with duplicates.");
        $this->newLine();

        $toDelete = [];

        foreach ($duplicates as $dup) {
            $employees = Employee::where('user_id', $dup->user_id)
                ->orderBy('created_at', 'asc') // Oldest first
                ->get();

            $original = $employees->shift(); // Keep the first one

            foreach ($employees as $duplicate) {
                $toDelete[] = $duplicate;
                $this->line("Found duplicate for User ID {$dup->user_id}:");
                $this->line("  - Original: ID {$original->id} ({$original->created_at})");
                $this->line("  - Duplicate: ID {$duplicate->id} ({$duplicate->created_at}) [To Delete]");
            }
        }

        $this->newLine();
        $this->info("Total records to delete: " . count($toDelete));

        if ($dryRun) {
            $this->info("🔍 DRY RUN - No changes made.");
            return 0;
        }

        if (!$this->option('force') && !$this->confirm("⚠️  Are you sure you want to delete these " . count($toDelete) . " records?")) {
            $this->info("Operation cancelled.");
            return 0;
        }

        $this->info("🔄 Deleting duplicates...");

        DB::transaction(function () use ($toDelete) {
            foreach ($toDelete as $employee) {
                $this->line("Deleting Employee ID {$employee->id}...");
                $employee->delete();
            }
        });

        $this->info("✅ Cleanup complete!");
        return 0;
    }
}
