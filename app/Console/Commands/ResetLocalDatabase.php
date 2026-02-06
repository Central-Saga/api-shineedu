<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class ResetLocalDatabase extends Command
{
    protected $signature = 'db:reset-local {--force : Skip confirmation}';
    protected $description = 'Reset entire local database (murid, users, and related tables). LOCAL ONLY.';

    public function handle()
    {
        // SAFETY: Only allow in local environment
        if (!App::environment('local')) {
            $this->error('❌ ABORT: This command can ONLY run in local environment!');
            $this->error("Current environment: " . App::environment());
            return 1;
        }

        if (
            !$this->option('force') &&
            !$this->confirm('⚠️  This will DELETE ALL DATA including users. Continue?')
        ) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Tables to truncate in order (respect FK constraints)
        $tables = [
            'sesi_murid_assignment',
            'sesi_murid_materi',
            'sesi_logbook_murid',
            'sesi_absensi_murid',
            'paket_murid_ledger',
            'paket_murid',
            'enrollment',
            'murid',
            'model_has_roles',
            'model_has_permissions',
            'users',
        ];

        $this->info('🔄 Resetting local database...');
        $this->newLine();

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $count = DB::table($table)->count();
                    DB::table($table)->truncate();
                    $this->line("  ✓ Truncated: {$table} ({$count} rows)");
                } else {
                    $this->line("  - Skipped (not exists): {$table}");
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->newLine();
            $this->info('✅ Local database reset complete!');
            $this->info('💡 Run "php artisan db:seed" to re-seed if needed.');
            return 0;
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
