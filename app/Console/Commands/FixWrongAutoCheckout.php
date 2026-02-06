<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\HR\Domain\Models\Absensi;
use Carbon\Carbon;

class FixWrongAutoCheckout extends Command
{
    protected $signature = 'fix:start-fresh';
    protected $description = 'Revert premature auto-checkout for today';

    public function handle()
    {
        $today = Carbon::now()->toDateString();
        $checkoutTime = '20:20:00';

        $this->info("🔍 Finding records auto-checked out today ({$today}) at {$checkoutTime}...");

        $records = Absensi::whereDate('tanggal', $today)
            ->whereTime('jam_pulang', $checkoutTime)
            ->where('sumber_absen', 'LIKE', '%(Auto-Checkout)%')
            ->get();

        if ($records->isEmpty()) {
            $this->info("✅ No incorrect auto-checkout records found for today.");
            return;
        }

        $this->info("Found {$records->count()} records to revert.");

        if (!$this->confirm("Are you sure you want to revert these records? They will be set to 'ongoing' (jam_pulang = null).")) {
            return;
        }

        foreach ($records as $record) {
            // Remove (Auto-Checkout) from sumber_absen
            $sumber = str_replace(' (Auto-Checkout)', '', $record->sumber_absen);

            // Remove auto-checkout note
            $catatan = $record->catatan;
            $catatan = str_replace(' [Auto-checkout by system at 20:20]', '', $catatan);
            $catatan = str_replace('Auto-checkout by system at 20:20', '', $catatan);

            $record->update([
                'jam_pulang' => null,
                'durasi' => null,
                'sumber_absen' => trim($sumber),
                'catatan' => trim($catatan) ?: null,
            ]);

            $this->line("Reverted record ID: {$record->id} (Karyawan ID: {$record->karyawan_id})");
        }

        $this->info("✅ Successfully reverted records.");
    }
}
