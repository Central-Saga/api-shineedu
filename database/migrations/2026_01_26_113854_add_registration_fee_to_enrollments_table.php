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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->decimal('biaya_pendaftaran_amount', 15, 2)->default(0)->after('tanggal_selesai');
            $table->string('biaya_pendaftaran_status')->default('UNPAID')->after('biaya_pendaftaran_amount'); // UNPAID, PAID, WAIVED
            $table->date('biaya_pendaftaran_due_date')->nullable()->after('biaya_pendaftaran_status');
            $table->unsignedBigInteger('registration_fee_transaction_id')->nullable()->after('biaya_pendaftaran_due_date');
            // Not making FK yet as requested ("TANPA membangun modul transaksi dulu/link later")
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'biaya_pendaftaran_amount',
                'biaya_pendaftaran_status',
                'biaya_pendaftaran_due_date',
                'registration_fee_transaction_id'
            ]);
        });
    }
};
