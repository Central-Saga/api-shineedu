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
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->foreignId('kelas_id')
                ->nullable()
                ->after('id')
                ->constrained('kelas')
                ->onDelete('set null');

            // Add index (FK usually adds index, but good to be explicit for queries)
            // Laravel constrained() adds foreign key constraint but not always an index depending on DB.
            // But we can trust constrained() for FK.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropColumn('kelas_id');
        });
    }
};
