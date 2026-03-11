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
        Schema::table('program', function (Blueprint $table) {
            if (!Schema::hasColumn('program', 'image')) {
                $table->string('image')->nullable()->after('deskripsi');
            }
            if (!Schema::hasColumn('program', 'fitur')) {
                $table->json('fitur')->nullable()->after('image');
            }
            if (!Schema::hasColumn('program', 'is_highlight')) {
                $table->boolean('is_highlight')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program', function (Blueprint $table) {
            $table->dropColumn(['image', 'fitur', 'is_highlight']);
        });
    }
};
