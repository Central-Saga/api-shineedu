<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_blog_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('landing_blog_posts')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_blog_assets');
    }
};
