<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\HR\Domain\Models\Employee;
use App\Modules\Identity\Domain\Models\User;
use Carbon\Carbon;

class InvestigateKaryawan extends Command
{
    protected $signature = 'investigate:karyawan {--hours=24 : Look back N hours}';
    protected $description = 'Investigate duplicate employee data created recently';

    public function handle()
    {
        $hours = $this->option('hours');
        $since = Carbon::now()->subHours($hours);

        $this->info("🔍 Investigating employees created since {$since->toDateTimeString()}...");

        // 1. Check recent employees
        $recentEmployees = Employee::with('user')
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->info("Found {$recentEmployees->count()} employees created in the last {$hours} hours.");

        if ($recentEmployees->isNotEmpty()) {
            $this->table(
                ['ID', 'User ID', 'Name', 'Kode', 'Created At'],
                $recentEmployees->map(fn($e) => [
                    $e->id,
                    $e->user_id,
                    $e->user->name ?? 'N/A',
                    $e->kode_karyawan,
                    $e->created_at->toDateTimeString()
                ])
            );
        }

        // 2. Check for duplicates by User ID (The most likely cause if seeder ran twice)
        $this->newLine();
        $this->info("🔍 Checking for duplicates by User ID...");

        $duplicates = Employee::select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info("✅ No duplicate User IDs found in Employee table.");
        } else {
            $this->error("🚨 Found {$duplicates->count()} users with multiple employee records!");

            foreach ($duplicates as $dup) {
                $user = User::find($dup->user_id);
                $employees = Employee::where('user_id', $dup->user_id)->get();

                $this->newLine();
                $this->line("User: {$user->name} ({$user->email}) (ID: {$user->id})");
                $this->table(
                    ['Employee ID', 'Kode', 'Status', 'Created At'],
                    $employees->map(fn($e) => [
                        $e->id,
                        $e->kode_karyawan,
                        is_object($e->status) && enum_exists(get_class($e->status)) ? $e->status->value : $e->status, // Handle Enum
                        $e->created_at->toDateTimeString()
                    ])
                );
            }
        }

        // 3. Check for duplicates by Name/Email if User ID is allowed to be different (less likely for this specific bug)
        // Skipping for now, focusing on the User ID <> Employee 1:N cardinality violation since Karyawan usually 1:1 with User.
    }
}
