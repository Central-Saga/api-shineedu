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
        Schema::create('pengaturan_cuti_rules', function (Blueprint $table) {
            $table->id();
            $table->string('kategori_karyawan'); // Tetap, Kontrak, Freelance
            $table->string('subtipe_kontrak')->nullable(); // Part time, Full time (only for Kontrak)
            $table->string('jenis'); // cuti, izin, sakit
            $table->string('periode')->default('bulanan'); // bulanan, tahunan
            $table->integer('maksimal_pengajuan')->nullable(); // NULL = unlimited
            $table->integer('minimal_hari_pengajuan')->default(0);
            $table->string('potongan_tipe')->default('none'); // per_hari, flat, none
            $table->decimal('potongan_nilai', 12, 2)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_cuti_rules');
    }
};
