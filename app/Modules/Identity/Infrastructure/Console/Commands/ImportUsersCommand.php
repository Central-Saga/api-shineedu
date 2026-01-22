<?php

namespace App\Modules\Identity\Infrastructure\Console\Commands;

use App\Modules\Identity\Application\Services\UserImportService;
use Illuminate\Console\Command;

class ImportUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'identity:import-users
                            {file : The path to the CSV file}
                            {--update : Update existing users if they exist}
                            {--force-password : Overwrite passwords for existing users during update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import user data from a CSV file into the users table';

    /**
     * Execute the console command.
     */
    public function handle(UserImportService $importService)
    {
        $filePath = $this->argument('file');
        $updateExisting = $this->option('update');
        $forcePassword = $this->option('force-password');

        if (!str_starts_with($filePath, '/')) {
            $filePath = getcwd() . '/' . $filePath;
        }

        $this->info("Starting import from: {$filePath}");
        if ($updateExisting) {
            $this->warn("Update mode is ENABLED.");
        }
        if ($forcePassword) {
            $this->warn("Password overwrite is ENABLED.");
        }

        $report = $importService->import($filePath, $updateExisting, $forcePassword);

        // Print logs
        foreach ($report['logs'] as $log) {
            $this->line($log);
        }

        $this->newLine();
        $this->info("Import Summary:");
        $this->table(
            ['Total Rows', 'Inserted', 'Updated', 'Skipped', 'Validation Errors'],
            [[
                $report['total_rows'],
                $report['inserted_count'],
                $report['updated_count'],
                $report['skipped_count'],
                count($report['validation_errors']),
            ]]
        );

        if (!empty($report['validation_errors'])) {
            $this->error("Validation Errors Encountered:");
            foreach ($report['validation_errors'] as $error) {
                $this->error("- {$error}");
            }
        }

        return count($report['validation_errors']) > 0 ? 1 : 0;
    }
}
