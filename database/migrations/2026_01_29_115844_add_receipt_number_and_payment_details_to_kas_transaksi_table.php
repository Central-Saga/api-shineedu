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
        Schema::table('kas_transaksi', function (Blueprint $table) {
            $table->string('receipt_number', 20)->nullable()->unique()->after('id');
            $table->json('payment_details')->nullable()->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_transaksi', function (Blueprint $table) {
            $table->dropColumn(['receipt_number', 'payment_details']);
        });
    }
};
