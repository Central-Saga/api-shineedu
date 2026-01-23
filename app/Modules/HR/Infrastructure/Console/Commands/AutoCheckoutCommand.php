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
    protected $description = 'Automatically checkout employees who forgot to checkout by 20:30 WITA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $checkoutTime = '20:30:00';

        $records = Absensi::where('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->get();

        if ($records->isEmpty()) {
            $this->info('No pending check-outs found for today.');
            return;
        }

        $count = 0;
        foreach ($records as $record) {
            $jamMasuk = Carbon::parse($record->jam_masuk);
            $jamPulang = Carbon::parse($today . ' ' . $checkoutTime);

            // If jam_masuk is somehow after 20:30 (shouldn't happen for today's records)
            // we use the jam_masuk as checkout to avoid negative duration
            if ($jamMasuk->greaterThan($jamPulang)) {
                $jamPulang = $jamMasuk;
            }

            $record->update([
                'jam_pulang' => $jamPulang,
                'durasi' => $jamMasuk->diffInMinutes($jamPulang),
                'sumber_absen' => $record->sumber_absen . ' (Auto-Checkout)',
                'catatan' => $record->catatan ? $record->catatan . ' [Auto-checkout by system]' : 'Auto-checkout by system',
            ]);

            $count++;
        }

        $this->info("Successfully checked out {$count} records.");
        Log::info("Absensi Auto-Checkout: Checked out {$count} records for {$today}");
    }
}
