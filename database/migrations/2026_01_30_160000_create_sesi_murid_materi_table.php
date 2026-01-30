<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sesi_murid_materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_jadwal_kerja_id')
                ->constrained('realisasi_jadwal_kerja')
                ->cascadeOnDelete();
            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->cascadeOnDelete();
            $table->foreignId('materi_modul_id')
                ->constrained('materi_modul')
                ->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('accessed_at')->nullable();

            // Prevent duplicate assignments
            $table->unique(
                ['realisasi_jadwal_kerja_id', 'enrollment_id', 'materi_modul_id'],
                'unique_sesi_murid_materi'
            );

            $table->index('realisasi_jadwal_kerja_id');
            $table->index('enrollment_id');
            $table->index('materi_modul_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_murid_materi');
    }
};
