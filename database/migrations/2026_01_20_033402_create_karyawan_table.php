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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('kode_karyawan')->unique();
            $table->string('kategori_karyawan')->nullable();
            $table->string('subtipe_kontrak')->nullable();
            $table->string('tipe_gaji')->nullable();
            $table->decimal('gaji_pokok', 12, 2)->nullable();
            $table->string('bank_nama')->nullable();
            $table->string('bank_no_rekening')->nullable();
            $table->string('nomor_hp', 30)->nullable();
            $table->string('alamat', 500)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
