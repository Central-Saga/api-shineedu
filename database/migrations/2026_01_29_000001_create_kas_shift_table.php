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
        Schema::create('kas_shift', function (Blueprint $table) {
            $table->id();

            // Shift timing
            $table->datetime('opened_at')->useCurrent();
            $table->datetime('closed_at')->nullable();

            // Status
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');

            // Cash balances
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('closing_balance', 14, 2)->nullable();
            $table->decimal('expected_cash', 14, 2)->nullable();
            $table->decimal('actual_cash', 14, 2)->nullable();
            $table->decimal('variance', 14, 2)->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('opened_by')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('opened_at');
        });

        // Add shift_id to kas_transaksi
        Schema::table('kas_transaksi', function (Blueprint $table) {
            $table->foreignId('shift_id')
                ->nullable()
                ->after('id')
                ->constrained('kas_shift')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_transaksi', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });

        Schema::dropIfExists('kas_shift');
    }
};
