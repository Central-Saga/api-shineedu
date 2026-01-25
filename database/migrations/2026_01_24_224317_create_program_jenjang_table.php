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
        Schema::create('program_jenjang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('program')->onDelete('cascade');
            $table->foreignId('jenjang_id')->constrained('jenjang')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['program_id', 'jenjang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_jenjang');
    }
};
