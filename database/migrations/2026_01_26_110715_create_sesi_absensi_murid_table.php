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
        Schema::create('sesi_absensi_murid', function (Blueprint $table) {
            $table->id();

            $table->foreignId('realisasi_jadwal_kerja_id')
                ->constrained('realisasi_jadwal_kerja')
                ->onDelete('cascade');

            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->onDelete('cascade');

            $table->string('status')->default('HADIR'); // HADIR, IZIN, SAKIT, ALPHA, BATAL
            $table->text('catatan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['realisasi_jadwal_kerja_id', 'enrollment_id'], 'unique_sesi_enrollment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_absensi_murid');
    }
};
