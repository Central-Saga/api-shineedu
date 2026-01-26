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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_enrollment')->unique()->nullable(); // Optional auto-gen

            // Foreign Keys
            $table->foreignId('murid_id')->constrained('murid')->cascadeOnDelete();

            // Catalog References
            $table->foreignId('program_id')->constrained('program');
            $table->foreignId('jenjang_id')->constrained('jenjang');
            $table->foreignId('paket_id')->constrained('paket');

            $table->unsignedSmallInteger('jumlah_siswa')->default(1);
            $table->decimal('harga_final', 15, 2);

            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            $table->string('status')->default('Aktif')->comment('Aktif, Pause, Selesai, Cancel');
            $table->text('catatan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
