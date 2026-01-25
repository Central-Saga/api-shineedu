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
        // 5) Tabel paket_harga
        Schema::create('paket_harga', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_id')->constrained('program')->onDelete('cascade');
            $table->foreignId('jenjang_id')->constrained('jenjang')->onDelete('cascade');
            $table->foreignId('paket_id')->constrained('paket')->onDelete('cascade');

            $table->unsignedSmallInteger('min_siswa')->default(1);
            $table->unsignedSmallInteger('max_siswa')->default(1);
            $table->decimal('harga', 15, 2)->default(0);

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->string('status')->default('Aktif');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['program_id', 'jenjang_id', 'paket_id']);
            $table->index(['min_siswa', 'max_siswa']);

            // Unique Constraint with a clear name
            // (program_id, jenjang_id, paket_id, min_siswa, max_siswa, effective_from)
            $table->unique(
                ['program_id', 'jenjang_id', 'paket_id', 'min_siswa', 'max_siswa', 'effective_from'],
                'unique_paket_harga_config'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_harga');
    }
};
