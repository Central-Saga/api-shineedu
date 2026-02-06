<?php

namespace App\Console\Commands;

use App\Modules\HR\Domain\Models\Absensi;
use Illuminate\Console\Command;

class DebugAutoCheckout extends Command
{
    protected $signature = 'debug:auto-checkout';
    protected $description = 'Debug auto-checkout records';

    public function handle()
    {
        $records = Absensi::where('catatan', 'like', '%Auto-checkout%')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($records as $record) {
            $this->info("=== Record ID: {$record->id} ===");
            $this->info("Karyawan ID: {$record->karyawan_id}");
            $this->info("Tanggal: {$record->tanggal}");
            $this->info("Jam Masuk: {$record->jam_masuk}");
            $this->info("Jam Pulang: {$record->jam_pulang}");
            $this->info("Durasi: {$record->durasi} menit");
            $this->info("Sumber: {$record->sumber_absen}");
            $this->info("---");
        }
    }
}
