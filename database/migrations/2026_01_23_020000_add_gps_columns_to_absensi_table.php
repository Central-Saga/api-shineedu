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
        Schema::table('absensi', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('status_kehadiran');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->text('qr_code_data')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'qr_code_data']);
        });
    }
};
