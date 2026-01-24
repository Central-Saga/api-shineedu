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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->foreign('karyawan_id')->references('id')->on('karyawan')->onDelete('cascade');

            $table->integer('bulan');
            $table->integer('tahun');

            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('total_fee_mengajar', 15, 2)->default(0);
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->decimal('gaji_bersih', 15, 2)->default(0);

            $table->json('detail_potongan')->nullable();
            $table->json('detail_pendapatan')->nullable();

            $table->string('status')->default('draft'); // draft, generated, approved, paid
            $table->date('tanggal_pembayaran')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['karyawan_id', 'bulan', 'tahun'], 'payroll_unique_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
