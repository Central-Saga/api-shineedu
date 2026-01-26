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
        // Level 1: Logbook Sesi (Class level)
        Schema::create('sesi_logbook', function (Blueprint $table) {
            $table->id();

            $table->foreignId('realisasi_jadwal_kerja_id')
                ->constrained('realisasi_jadwal_kerja')
                ->onDelete('cascade');

            $table->text('ringkasan')->nullable();
            $table->text('materi')->nullable();
            $table->text('homework')->nullable();
            $table->text('catatan_pengajar')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->unique('realisasi_jadwal_kerja_id');
        });

        // Level 2: Logbook Murid (Student level)
        Schema::create('sesi_logbook_murid', function (Blueprint $table) {
            $table->id();

            $table->foreignId('realisasi_jadwal_kerja_id')
                ->constrained('realisasi_jadwal_kerja')
                ->onDelete('cascade');

            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->onDelete('cascade');

            $table->text('catatan_perkembangan')->nullable();
            $table->text('kesulitan')->nullable();
            $table->text('target_next')->nullable();
            $table->text('tugas_individu')->nullable();
            $table->decimal('nilai_opsional', 8, 2)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['realisasi_jadwal_kerja_id', 'enrollment_id'], 'unique_sesi_logbook_enrollment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_logbook_murid');
        Schema::dropIfExists('sesi_logbook');
    }
};
