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
        Schema::create('assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('realisasi_jadwal_kerja_id')->nullable()
                ->constrained('realisasi_jadwal_kerja')->nullOnDelete();
            $table->foreignId('materi_modul_id')->nullable()
                ->constrained('materi_modul')->nullOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->enum('status', ['ASSIGNED', 'SUBMITTED', 'REVIEWED', 'CLOSED'])->default('ASSIGNED');
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['enrollment_id', 'realisasi_jadwal_kerja_id']);
            $table->index('status');
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment');
    }
};
