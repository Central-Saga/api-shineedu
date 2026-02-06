<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateMuridTable extends Command
{
    protected $signature = 'murid:truncate-safe {--force : Force truncation without confirmation}';
    protected $description = 'Safely truncate the murid table only, preserving other data.';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('DANGER: This will delete ALL data in the `murid` table. Are you sure you want to proceed?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Starting safe truncation of `murid` table...');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            DB::table('murid')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('Murid table truncated successfully.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error truncating table: ' . $e->getMessage());
        }
    }
}
