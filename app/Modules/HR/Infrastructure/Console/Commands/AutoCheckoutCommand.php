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
        $now = Carbon::now();

        Log::info("Auto-Checkout Command Started", [
            'current_time' => $now->toDateTimeString(),
            'checkout_time' => $checkoutTime,
        ]);

        // Find all records where jam_masuk is present but jam_pulang is null
        // We include older records as well in case the command didn't run or failed
        $records = Absensi::whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->where('tanggal', '<=', Carbon::today())
            ->get();

        if ($records->isEmpty()) {
            $this->info('No pending check-outs found.');
            Log::info("Auto-Checkout: No pending check-outs found.");
            return;
        }

        $this->info("Found {$records->count()} pending check-outs.");
        Log::info("Auto-Checkout: Found {$records->count()} pending records.");

        $count = 0;
        $skipped = 0;
        $today = $now->toDateString();

        foreach ($records as $record) {
            $tanggal = $record->tanggal->toDateString();

            // Safety check: Only auto-checkout today's records if it's strictly past 20:20
            if ($tanggal === $today && $now->format('H:i:s') < $checkoutTime) {
                $skipped++;
                Log::debug("Auto-Checkout: Skipped today's record (too early)", [
                    'karyawan_id' => $record->karyawan_id,
                    'tanggal' => $tanggal,
                    'current_time' => $now->format('H:i:s'),
                ]);
                continue;
            }

            $jamMasuk = Carbon::parse($record->jam_masuk);
            $jamPulang = Carbon::parse($tanggal . ' ' . $checkoutTime);

            // If jam_masuk is after 20:20 (e.g. night shift or late input),
            // we use the actual time or jam_masuk to avoid negative duration.
            // But for auto-checkout, we follow the user's rule of 20:20.
            if ($jamMasuk->greaterThan($jamPulang)) {
                $jamPulang = $jamMasuk->copy()->addMinutes(1); // Set 1 min after masuk as fallback
            }

            $durasi = $jamMasuk->diffInMinutes($jamPulang);

            $record->update([
                'jam_pulang' => $jamPulang,
                'durasi' => $durasi,
                'sumber_absen' => $record->sumber_absen . ' (Auto-Checkout)',
                'catatan' => $record->catatan ? $record->catatan . ' [Auto-checkout by system at 20:20]' : 'Auto-checkout by system at 20:20',
            ]);

            Log::info("Auto-Checkout: Checked out record", [
                'karyawan_id' => $record->karyawan_id,
                'tanggal' => $tanggal,
                'jam_masuk' => $jamMasuk->format('H:i:s'),
                'jam_pulang' => $jamPulang->format('H:i:s'),
                'durasi' => $durasi,
            ]);

            $count++;
        }

        $this->info("Successfully checked out {$count} records. Skipped {$skipped} records.");
        Log::info("Auto-Checkout Command Completed", [
            'checked_out' => $count,
            'skipped' => $skipped,
            'total_found' => $records->count(),
        ]);
    }
}
