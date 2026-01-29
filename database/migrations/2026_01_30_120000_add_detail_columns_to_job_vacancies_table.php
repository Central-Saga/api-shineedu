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
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->string('employment_type')->nullable()->after('location'); // Full-time, Part-time, dll
            $table->text('description')->nullable()->after('employment_type');
            $table->date('posted_at')->nullable()->after('description');
            $table->date('end_at')->nullable()->after('posted_at');
            $table->json('requirements')->nullable()->after('end_at');   // array of strings
            $table->json('responsibilities')->nullable()->after('requirements');
            $table->json('benefits')->nullable()->after('responsibilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->dropColumn([
                'employment_type',
                'description',
                'posted_at',
                'end_at',
                'requirements',
                'responsibilities',
                'benefits',
            ]);
        });
    }
};
