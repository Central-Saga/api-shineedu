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
            // Hapus kolom lama jika ada
            if (Schema::hasColumn('enrollments', 'saldo_managed_internally')) {
                $table->dropColumn('saldo_managed_internally');
            }

            // Sumber enrollment: INTERNAL (default), ISELLER, IMPORT
            if (!Schema::hasColumn('enrollments', 'sumber')) {
                $table->string('sumber')->default('INTERNAL')->after('status')
                    ->comment('INTERNAL, ISELLER, IMPORT');
            }
            
            // ID reference dari sistem external (opsional)
            if (!Schema::hasColumn('enrollments', 'external_reference_id')) {
                $table->string('external_reference_id')->nullable()->after('sumber')
                    ->comment('ID dari sistem i-seller atau external lainnya');
            }

            // Track saldo awal yang di-set manual (opsional)
            $table->integer('saldo_override')->nullable()->after('external_reference_id')
                ->comment('Saldo manual override. NULL = pakai sistem normal, -1 = unlimited/tidak dicek');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['sumber', 'external_reference_id', 'saldo_override']);
        });
    }
};
