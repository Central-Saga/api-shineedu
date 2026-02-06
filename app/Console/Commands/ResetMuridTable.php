<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetMuridTable extends Command
{
    protected $signature = 'reset:murid-table';
    protected $description = 'Reset murid table only (truncate all data)';

    public function handle()
    {
        if (!$this->confirm('⚠️  This will DELETE ALL data in murid table. Are you sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Resetting murid table...');

        try {
            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Truncate murid table
            DB::table('murid')->truncate();

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('✅ Murid table has been reset successfully!');
            $this->info('All murid data has been deleted.');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());

            // Make sure to re-enable foreign key checks even if error occurs
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return 1;
        }
    }
}
