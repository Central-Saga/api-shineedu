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
        Schema::table('realisasi_jadwal_kerja', function (Blueprint $table) {
            $table->foreignId('kelas_id')
                ->nullable()
                ->after('jadwal_kerja_id')
                ->constrained('kelas')
                ->onDelete('set null');

            $table->string('status_sesi')->default('TERJADWAL')->after('status'); // TERJADWAL, BERJALAN, SELESAI, BATAL, LIBUR
            $table->string('status_kehadiran_guru')->default('HADIR')->after('status_sesi'); // HADIR, IZIN, SAKIT, ALPHA, DIGANTI

            $table->time('jam_mulai_aktual')->nullable()->after('status_kehadiran_guru');
            $table->time('jam_selesai_aktual')->nullable()->after('jam_mulai_aktual');

            $table->timestamp('dibatalkan_pada')->nullable();
            $table->foreignId('dibatalkan_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->text('alasan_batal')->nullable();
            $table->boolean('is_hangus')->default(false);

            // Add unique constraint to prevent duplicates
            $table->unique(['jadwal_kerja_id', 'tanggal']);
        });

        // Re-ordering columns via separate change is hard, so we just use 'after' best effort.
        // Actually, let's just make sure we add the columns properly.
        // We need to drop the index in down()
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_jadwal_kerja', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropForeign(['dibatalkan_oleh']);
            $table->dropUnique(['jadwal_kerja_id', 'tanggal']);

            $table->dropColumn([
                'kelas_id',
                'status_sesi',
                'status_kehadiran_guru',
                'jam_mulai_aktual',
                'jam_selesai_aktual',
                'dibatalkan_pada',
                'dibatalkan_oleh',
                'alasan_batal',
                'is_hangus'
            ]);
        });
    }
};
