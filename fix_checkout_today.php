<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Modules\HR\Domain\Models\Absensi;
use Carbon\Carbon;

$today = Carbon::today()->toDateString();
$records = Absensi::where('tanggal', $today)
    ->where('catatan', 'like', '%Auto-checkout by system at 20:20%')
    ->get();

foreach ($records as $record) {
    echo "Restoring Absensi for ID: {$record->id}, Karyawan ID: {$record->karyawan_id}\n";
    $record->update([
        'jam_pulang' => null,
        'durasi' => null,
        'sumber_absen' => str_replace(' (Auto-Checkout)', '', $record->sumber_absen),
        'catatan' => null
    ]);
}
echo "Done restoring " . $records->count() . " records.\n";
