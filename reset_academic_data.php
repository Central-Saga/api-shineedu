<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'sesi_logbook_murid',
    'sesi_logbook',
    'sesi_absensi_murid',
    'sesi_murid_materi',
    'sesi_murid_assignment',
    'assignment_submission',
    'realisasi_jadwal_kerja',
    'jadwal_kerja',
    'kelas_enrollment',
    'kelas',
];

echo "Starting Targeted Reset (Academic Flow)...\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "Truncated: {$table}\n";
    } else {
        echo "Table not found: {$table} (Skipping)\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "\nSUCCESS: Academic data has been reset.\n";
echo "Protected data (Absensi, Cuti, Employees, Users, Murid, Enrollments) remains intact.\n";
