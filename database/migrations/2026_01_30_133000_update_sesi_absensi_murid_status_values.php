<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing attendance records to use new simplified status values
        // Old values: HADIR, IZIN, SAKIT, ALPHA, BATAL
        // New values: HADIR, TIDAK_HADIR, PINDAH_JADWAL

        DB::table('sesi_absensi_murid')
            ->whereIn('status', ['IZIN', 'SAKIT', 'ALPHA'])
            ->update(['status' => 'TIDAK_HADIR']);

        DB::table('sesi_absensi_murid')
            ->where('status', 'BATAL')
            ->update(['status' => 'PINDAH_JADWAL']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: Convert back to old values
        // Note: We can't perfectly restore IZIN, SAKIT, ALPHA distinction
        // All TIDAK_HADIR will become ALPHA

        DB::table('sesi_absensi_murid')
            ->where('status', 'TIDAK_HADIR')
            ->update(['status' => 'ALPHA']);

        DB::table('sesi_absensi_murid')
            ->where('status', 'PINDAH_JADWAL')
            ->update(['status' => 'BATAL']);
    }
};
