<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_gallery_items');
    }
};
