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
        Schema::create('kas_transaksi', function (Blueprint $table) {
            $table->id();

            // Transaction datetime
            $table->datetime('tanggal')->useCurrent();

            // Type: IN (income) or OUT (expense)
            $table->enum('type', ['IN', 'OUT']);

            // Amount (max 14 digits, 2 decimal places)
            $table->decimal('amount', 14, 2);

            // Payment method
            $table->enum('metode', ['CASH', 'TRANSFER', 'QRIS', 'E_WALLET', 'OTHER']);

            // Category
            $table->string('kategori', 100);

            // Description/notes
            $table->text('keterangan')->nullable();

            // Party name (customer/supplier)
            $table->string('pihak', 150)->nullable();

            // Reference tracking (polymorphic-like)
            $table->string('reference_type', 50)->nullable()->comment('enrollment_fee, paket_topup, manual');
            $table->unsignedBigInteger('reference_id')->nullable();

            // External reference (invoice number, transfer reference)
            $table->string('external_ref', 100)->nullable();

            // Idempotency key for preventing duplicate transactions
            $table->string('idempotency_key', 80)->nullable()->unique();

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();

            // Indexes for performance
            $table->index('tanggal', 'idx_tanggal');
            $table->index(['type', 'kategori'], 'idx_type_kategori');
            $table->index(['reference_type', 'reference_id'], 'idx_kas_transaksi_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_transaksi');
    }
};
