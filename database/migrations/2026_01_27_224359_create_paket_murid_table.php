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
        Schema::create('paket_murid', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->onDelete('cascade');

            $table->foreignId('paket_id')
                ->constrained('paket')
                ->onDelete('restrict'); // Prevent deletion of master catalog if in use

            // Status and dates
            $table->enum('status', ['AKTIF', 'SELESAI', 'PAUSED'])->default('AKTIF');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_berakhir')->nullable();

            // Notes
            $table->text('catatan')->nullable();

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();

            // Indexes for performance
            $table->index(['enrollment_id', 'status'], 'idx_enrollment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_murid');
    }
};
