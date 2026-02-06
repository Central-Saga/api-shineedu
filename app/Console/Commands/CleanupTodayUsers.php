<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\HR\Domain\Models\Employee;
use Illuminate\Support\Facades\DB;

class CleanupTodayUsers extends Command
{
    protected $signature = 'users:cleanup-today {--force : Force deletion without confirmation}';
    protected $description = 'Delete users created today (2026-02-06)';

    public function handle()
    {
        $today = '2026-02-06';

        $users = User::whereDate('created_at', $today)->get();

        if ($users->isEmpty()) {
            $this->info("No users found created on $today.");
            return;
        }

        $this->info("Found {$users->count()} users created on $today.");

        if (!$this->option('force') && !$this->confirm('Do you wish to delete all these users and their related employee data?')) {
            $this->info('Operation cancelled.');
            return;
        }

        DB::transaction(function () use ($users) {
            foreach ($users as $user) {
                // Delete related employee records first if exists (though cascading might handle it, better safe)
                $employee = Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    $this->info("Deleting Employee: {$employee->id} - {$user->name}");
                    $employee->delete();
                }

                $this->info("Deleting User: {$user->id} - {$user->name}");
                $user->delete();
            }
        });

        $this->info("Cleanup completed.");
    }
}
