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
        Schema::create('assignment_submission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignment')->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at');
            $table->text('content_text')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['SUBMITTED', 'REVISION_REQUESTED', 'ACCEPTED'])->default('SUBMITTED');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('feedback')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->index('assignment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submission');
    }
};
