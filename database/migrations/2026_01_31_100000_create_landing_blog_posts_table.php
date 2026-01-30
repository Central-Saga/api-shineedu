<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->longText('content')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->string('category', 64)->nullable()->index();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_blog_posts');
    }
};
