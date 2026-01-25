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
        Schema::create('murid', function (Blueprint $table) {
            $table->id();
            $table->string('kode_murid')->nullable()->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('no_hp');
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();

            // Foreign Keys
            $table->foreignId('jenjang_id')->nullable()->constrained('jenjang')->nullOnDelete();

            // Education Background
            $table->string('sekolah_asal')->nullable();
            $table->string('kelas_sekolah')->nullable();

            // Guardian Info
            $table->string('nama_wali')->nullable();
            $table->string('no_hp_wali')->nullable();
            $table->string('email_wali')->nullable();
            $table->string('hubungan_wali')->nullable();

            // Additional Info
            $table->text('catatan_khusus')->nullable();
            $table->text('kebutuhan_khusus')->nullable();

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
        Schema::dropIfExists('murid');
    }
};
