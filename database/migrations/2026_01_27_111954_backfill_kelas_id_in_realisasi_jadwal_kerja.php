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
        DB::statement("
            UPDATE realisasi_jadwal_kerja rjk
            JOIN jadwal_kerja jk ON jk.id = rjk.jadwal_kerja_id
            SET rjk.kelas_id = jk.kelas_id
            WHERE rjk.kelas_id IS NULL
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
