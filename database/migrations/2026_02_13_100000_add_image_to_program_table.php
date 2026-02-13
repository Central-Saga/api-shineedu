<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Gambar katalog program untuk tampilan di landing page.
     */
    public function up(): void
    {
        Schema::table('program', function (Blueprint $table) {
            $table->string('image', 500)->nullable()->after('deskripsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
