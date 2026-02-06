<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\HR\Domain\Models\Absensi;
use App\Modules\HR\Domain\Models\Employee; // Assuming model name
use Carbon\Carbon;

class CleanupInvalidAbsensi extends Command
{
    protected $signature = 'absensi:cleanup-invalid';
    protected $description = 'Cleanup invalid attendance records (Putri Aswi & Future records)';

    public function handle()
    {
        $today = Carbon::now()->toDateString();
        $now = Carbon::now();

        $this->info("Current Time: " . $now->toDateTimeString());

        // 1. Delete for specific user: "Putri Aswi"
        // Searching by name in related karyawan/user
        $employees = Employee::whereHas('user', function ($q) {
            $q->where('name', 'like', '%PUTRI ASWI%');
        })->get();

        if ($employees->isEmpty()) {
            $this->warn("No employee found matching 'PUTRI ASWI'.");
        } else {
            foreach ($employees as $emp) {
                $this->info("Found Employee: " . $emp->user->name . " (ID: {$emp->id})");

                $records = Absensi::where('karyawan_id', $emp->id)
                    ->whereDate('tanggal', $today)
                    ->get();

                foreach ($records as $record) {
                    $this->warn("Deleting attendance for {$emp->user->name} at {$record->jam_masuk}");
                    $record->delete();
                }
            }
        }

        // 2. Delete FUTURE check-ins (jam_masuk > now + buffer)
        // Buffer 5 mins to be safe against slight clock skews, but user says "16:26" vs "15:27" so it's hours.
        $futureRecords = Absensi::whereDate('tanggal', $today)
            ->where('jam_masuk', '>', $now->copy()->addMinutes(5))
            ->get();

        if ($futureRecords->count() > 0) {
            $this->info("Found {$futureRecords->count()} future records:");
            foreach ($futureRecords as $rec) {
                $this->warn("- ID: {$rec->id}, Name: {$rec->karyawan->user->name}, Masuk: {$rec->jam_masuk}");
                $rec->delete();
            }
        } else {
            $this->info("No other future records found.");
        }

        $this->info("Cleanup done.");
    }
}
