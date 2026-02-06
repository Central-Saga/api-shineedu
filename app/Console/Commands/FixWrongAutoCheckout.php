<?php

namespace App\Console\Commands;

use App\Modules\HR\Domain\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FixWrongAutoCheckout extends Command
{
    protected $signature = 'fix:wrong-auto-checkout';
    protected $description = 'Fix auto-checkout records that have wrong checkout time (1 minute after check-in)';

    public function handle()
    {
        $this->info('Finding records with wrong auto-checkout time...');

        // Find records where:
        // 1. Has Auto-checkout in catatan
        // 2. Durasi is very small (1-5 minutes) which indicates the bug
        $records = Absensi::where('catatan', 'like', '%Auto-checkout%')
            ->where('durasi', '<=', 5)
            ->whereNotNull('jam_masuk')
            ->whereNotNull('jam_pulang')
            ->get();

        if ($records->isEmpty()) {
            $this->info('No records found with wrong auto-checkout.');
            return 0;
        }

        $this->info("Found {$records->count()} records with potential wrong checkout time.");

        $fixed = 0;
        $checkoutTime = '20:20:00';

        foreach ($records as $record) {
            $tanggal = $record->tanggal->toDateString();

            // Parse jam_masuk - handle both TIME and DATETIME formats
            $jamMasukStr = $record->jam_masuk;
            if (strlen($jamMasukStr) > 8) {
                // Already has date, just parse it
                $jamMasuk = Carbon::parse($jamMasukStr);
            } else {
                // Only time, add the record's date
                $jamMasuk = Carbon::parse($tanggal . ' ' . $jamMasukStr);
            }

            $jamPulang = Carbon::parse($tanggal . ' ' . $checkoutTime);

            // If jam_masuk is after 20:20 (night shift), set checkout to next day
            if ($jamMasuk->greaterThan($jamPulang)) {
                $jamPulang = Carbon::parse($tanggal . ' ' . $checkoutTime)->addDay();
            }

            $durasi = $jamMasuk->diffInMinutes($jamPulang);

            // Only update if the new durasi is significantly different
            if (abs($durasi - $record->durasi) > 10) {
                $this->info("Fixing record ID {$record->id}:");
                $this->info("  Tanggal: {$tanggal}");
                $this->info("  Jam Masuk: {$record->jam_masuk}");
                $this->info("  Old Jam Pulang: {$record->jam_pulang} (durasi: {$record->durasi} menit)");
                $this->info("  New Jam Pulang: {$jamPulang->format('H:i:s')} (durasi: {$durasi} menit)");

                $record->update([
                    'jam_pulang' => $jamPulang->format('H:i:s'),
                    'durasi' => $durasi,
                ]);

                $fixed++;
            }
        }

        $this->info("✅ Fixed {$fixed} records!");
        return 0;
    }
}
