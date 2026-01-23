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
        Schema::table('pengaturan_cuti_rules', function (Blueprint $table) {
            $table->renameColumn('kategori_mapel', 'divisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_cuti_rules', function (Blueprint $table) {
            $table->renameColumn('divisi', 'kategori_mapel');
        });
    }
};
