<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixMuridNoHp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:murid-no-hp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix murid table to make no_hp nullable';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing murid table...');

        try {
            // First, mark the problematic migration as done
            $existingMigration = DB::table('migrations')
                ->where('migration', '2026_02_04_191406_create_password_reset_tokens_table')
                ->exists();

            if (!$existingMigration) {
                DB::table('migrations')->insert([
                    'migration' => '2026_02_04_191406_create_password_reset_tokens_table',
                    'batch' => 1,
                ]);
                $this->info('✓ Marked password_reset_tokens migration as done');
            }

            // Now run the actual fix
            Schema::table('murid', function ($table) {
                $table->string('no_hp')->nullable()->change();
            });

            // Mark our migration as done
            $ourMigration = DB::table('migrations')
                ->where('migration', '2026_02_06_002400_make_no_hp_nullable_in_murid_table')
                ->exists();

            if (!$ourMigration) {
                DB::table('migrations')->insert([
                    'migration' => '2026_02_06_002400_make_no_hp_nullable_in_murid_table',
                    'batch' => DB::table('migrations')->max('batch') + 1,
                ]);
                $this->info('✓ Marked no_hp nullable migration as done');
            }

            $this->info('✅ Successfully fixed murid table - no_hp is now nullable!');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
