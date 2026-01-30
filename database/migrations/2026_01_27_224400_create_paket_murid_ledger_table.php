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
        Schema::create('paket_murid_ledger', function (Blueprint $table) {
            $table->id();

            // Foreign Key to paket_murid
            $table->foreignId('paket_murid_id')
                ->constrained('paket_murid')
                ->onDelete('cascade');

            // Ledger details
            $table->datetime('tanggal')->useCurrent();
            $table->enum('type', ['TOPUP', 'USE', 'ADJUST', 'EXPIRE', 'REFUND']);
            $table->integer('qty')->comment('Positive for credit, negative for debit');

            // Reference tracking (polymorphic-like)
            $table->string('reference_type')->nullable()->comment('attendance, admin_adjust, purchase, etc.');
            $table->unsignedBigInteger('reference_id')->nullable();

            // Admin notes
            $table->text('reason')->nullable()->comment('Admin reason for adjustment/expire');

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamp('created_at')->useCurrent();

            // Indexes for performance
            $table->index('paket_murid_id', 'idx_paket_murid_id');
            $table->index(['reference_type', 'reference_id'], 'idx_paket_murid_ledger_reference');

            // CRITICAL: Prevent double-deduction for same attendance
            // Only one USE entry per attendance record
            $table->unique(['reference_type', 'reference_id', 'type'], 'unique_ref_use_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_murid_ledger');
    }
};
