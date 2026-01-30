<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite does not support UPDATE ... JOIN; use subquery (works on MySQL too).
        DB::statement("
            UPDATE realisasi_jadwal_kerja
            SET kelas_id = (
                SELECT jk.kelas_id
                FROM jadwal_kerja jk
                WHERE jk.id = realisasi_jadwal_kerja.jadwal_kerja_id
            )
            WHERE kelas_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed for backfill usually,
        // as we cannot know which ones were null before.
    }
};
