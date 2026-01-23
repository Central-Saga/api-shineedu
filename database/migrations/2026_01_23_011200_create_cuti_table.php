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
        Schema::create('cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->string('jenis'); // cuti, izin, sakit
            $table->string('status')->default('diajukan'); // diajukan, disetujui, ditolak, dibatalkan, pembatalan_diajukan
            $table->date('tanggal')->nullable(); // For single day or start of range if logic allows
            $table->date('start_date');
            $table->date('end_date');
            $table->text('keterangan')->nullable();

            // Approval & Potongan info snapshot
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('potongan_tipe')->nullable(); // Snapshot from rule
            $table->decimal('potongan_nilai', 12, 2)->nullable(); // Snapshot from rule

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuti');
    }
};
