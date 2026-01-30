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
        Schema::create('materi_modul_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materi_modul_id')->constrained('materi_modul')->cascadeOnDelete();
            $table->enum('type', ['FILE', 'URL']);
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('order_no')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('materi_modul_id');
            $table->index('order_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_modul_item');
    }
};
