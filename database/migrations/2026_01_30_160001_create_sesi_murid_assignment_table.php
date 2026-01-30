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
        Schema::create('sesi_murid_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_jadwal_kerja_id')
                ->constrained('realisasi_jadwal_kerja')
                ->cascadeOnDelete();
            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->cascadeOnDelete();
            $table->foreignId('assignment_id')
                ->constrained('assignment')
                ->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();

            // Prevent duplicate assignments
            $table->unique(
                ['realisasi_jadwal_kerja_id', 'enrollment_id', 'assignment_id'],
                'unique_sesi_murid_assignment'
            );

            $table->index('realisasi_jadwal_kerja_id');
            $table->index('enrollment_id');
            $table->index('assignment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_murid_assignment');
    }
};
