<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // english, computer
            $table->json('data_mapping')->nullable(); // coordinates for printing
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('assessment_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('certificate_template_id')->constrained('assessment_certificate_templates');

            // Linking to Karyawan (Employee) as requested
            $table->unsignedBigInteger('teacher_karyawan_id')->nullable();
            $table->foreign('teacher_karyawan_id')->references('id')->on('karyawan')->nullOnDelete();

            $table->json('scores')->nullable(); // Detailed scores input

            $table->decimal('total_score', 8, 2)->nullable();
            $table->decimal('average_score', 5, 2)->nullable();

            $table->string('predicate')->nullable(); // EXCELLENT, A, etc.
            $table->string('certificate_level')->nullable(); // A1, A2...

            $table->timestamp('generated_at')->nullable();

            $table->string('certificate_no')->nullable()->unique()->index();
            // Indexes for performance
            $table->index(['enrollment_id', 'certificate_template_id']);
            $table->index('teacher_karyawan_id');
            $table->index('generated_at');

            $table->json('payload_snapshot')->nullable(); // Snapshot of student/template data at generation time

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_grades');
        Schema::dropIfExists('assessment_certificate_templates');
    }
};
