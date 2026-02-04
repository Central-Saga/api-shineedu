<?php

namespace App\Modules\HR\Infrastructure\Console\Commands;

use App\Modules\HR\Domain\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCheckoutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:auto-checkout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically checkout employees who forgot to checkout by 20:20 WITA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $checkoutTime = '20:20:00';

        // Find all records where jam_masuk is present but jam_pulang is null
        // We include older records as well in case the command didn't run or failed
        $records = Absensi::whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->where('tanggal', '<=', Carbon::today())
            ->get();

        if ($records->isEmpty()) {
            $this->info('No pending check-outs found.');
            return;
        }

        $count = 0;
        foreach ($records as $record) {
            $tanggal = $record->tanggal->toDateString();
            $jamMasuk = Carbon::parse($record->jam_masuk);
            $jamPulang = Carbon::parse($tanggal . ' ' . $checkoutTime);

            // If jam_masuk is after 20:20 (e.g. night shift or late input),
            // we use the actual time or jam_masuk to avoid negative duration.
            // But for auto-checkout, we follow the user's rule of 20:20.
            if ($jamMasuk->greaterThan($jamPulang)) {
                $jamPulang = $jamMasuk->copy()->addMinutes(1); // Set 1 min after masuk as fallback
            }

            $record->update([
                'jam_pulang' => $jamPulang,
                'durasi' => $jamMasuk->diffInMinutes($jamPulang),
                'sumber_absen' => $record->sumber_absen . ' (Auto-Checkout)',
                'catatan' => $record->catatan ? $record->catatan . ' [Auto-checkout by system at 20:20]' : 'Auto-checkout by system at 20:20',
            ]);

            $count++;
        }

        $this->info("Successfully checked out {$count} records.");
        Log::info("Absensi Auto-Checkout: Checked out {$count} records.");
    }
}
