<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_blog_posts', function (Blueprint $table) {
            $table->string('featured_image_path')->nullable()->after('content');
        });
        Schema::table('landing_blog_posts', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }

    public function down(): void
    {
        Schema::table('landing_blog_posts', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });
        Schema::table('landing_blog_posts', function (Blueprint $table) {
            $table->dropColumn('featured_image_path');
        });
    }
};
