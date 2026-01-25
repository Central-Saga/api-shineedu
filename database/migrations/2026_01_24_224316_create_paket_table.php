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
        Schema::create('paket', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('tipe'); // REGULER | PRIVATE | BUNDLE
            $table->unsignedSmallInteger('pertemuan_per_bulan')->nullable();
            $table->unsignedSmallInteger('durasi_menit')->default(70);
            $table->boolean('boleh_mix_mapel')->default(false);
            $table->unsignedTinyInteger('max_mapel')->nullable();
            $table->boolean('bisa_tambah_pertemuan')->default(true);
            $table->boolean('bisa_ganti_hari')->default(true);
            $table->string('status')->default('Aktif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket');
    }
};
