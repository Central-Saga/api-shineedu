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
        Schema::create('realisasi_jadwal_kerja', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');

            $table->foreignId('jadwal_kerja_id')
                ->constrained('jadwal_kerja')
                ->onDelete('cascade');

            // Status Kehadiran: HADIR, IZIN, SAKIT, BATAL, LIBUR, etc.
            $table->string('status');

            // Yang menyetujui / memverifikasi
            $table->foreignId('disetujui_oleh')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->string('sumber')->default('MANUAL'); // MANUAL, SYSTEM (Auto-generate)
            $table->text('catatan')->nullable();

            // Override (jika berbeda dari jadwal master)
            $table->string('ruangan_kelas')->nullable();
            $table->foreignId('guru_pengajar_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignId('guru_pengganti_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_jadwal_kerja');
    }
};
