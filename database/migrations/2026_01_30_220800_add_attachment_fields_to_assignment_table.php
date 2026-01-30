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
        Schema::table('assignment', function (Blueprint $table) {
            $table->enum('attachment_type', ['NONE', 'FILE', 'URL'])->default('NONE')->after('instructions');
            $table->string('attachment_url')->nullable()->after('attachment_type');
            $table->string('attachment_path')->nullable()->after('attachment_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment', function (Blueprint $table) {
            $table->dropColumn(['attachment_type', 'attachment_url', 'attachment_path']);
        });
    }
};
