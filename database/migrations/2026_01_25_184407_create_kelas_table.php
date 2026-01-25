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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kelas')->unique();
            $table->string('nama_kelas');
            $table->foreignId('program_id')->constrained('program');
            $table->foreignId('jenjang_id')->constrained('jenjang');
            $table->enum('tipe_kelas', ['REGULER', 'PRIVATE'])->default('REGULER');
            $table->enum('mode_private', ['INDIVIDU', 'GROUP'])->nullable();
            $table->integer('kapasitas')->nullable();
            $table->enum('status', ['Draft', 'Aktif', 'Selesai', 'Non Aktif'])->default('Aktif');
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->string('ruangan_default')->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
