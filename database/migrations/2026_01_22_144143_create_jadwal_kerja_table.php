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
        Schema::create('jadwal_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('kategori'); // e.g., 'REGULER', 'PRIVAT'
            $table->string('mata_pelajaran')->nullable();
            $table->string('hari'); // Senin, Selasa, etc.
            $table->string('nomor_sesi')->nullable(); // Sesi 1, Sesi 2
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->decimal('tarif', 15, 2)->default(0);
            $table->string('status')->default('Aktif'); // Aktif, Non Aktif
            $table->string('ruangan_kelas')->nullable();

            // Relasi ke User (Guru)
            // Menggunakan users.id karena user login system
            $table->foreignId('guru_pengajar_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kerja');
    }
};
