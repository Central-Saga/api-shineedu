<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Student\Domain\Models\Murid;

class CleanupTestUsers extends Command
{
    protected $signature = 'cleanup:test-users {--dry-run : Preview what will be deleted} {--force : Skip confirmation}';
    protected $description = 'Remove test users with example.com/example.net/example.org emails';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Find test users
        $testDomains = ['@example.com', '@example.net', '@example.org'];
        $query = User::query();

        foreach ($testDomains as $domain) {
            $query->orWhere('email', 'like', '%' . $domain);
        }

        $testUsers = $query->get();

        if ($testUsers->isEmpty()) {
            $this->info('✅ No test users found.');
            return 0;
        }

        $this->info("Found {$testUsers->count()} test users:");
        $this->newLine();

        $this->table(
            ['ID', 'Name', 'Email', 'Created At'],
            $testUsers->map(fn($u) => [$u->id, $u->name, $u->email, $u->created_at])->toArray()
        );

        if ($dryRun) {
            $this->info('🔍 DRY RUN - No changes made.');
            return 0;
        }

        if (
            !$this->option('force') &&
            !$this->confirm('⚠️  Delete these test users and their related murid data?')
        ) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('🔄 Deleting test users...');

        try {
            DB::transaction(function () use ($testUsers) {
                foreach ($testUsers as $user) {
                    // Delete related murid first
                    $murid = Murid::where('user_id', $user->id)->first();
                    if ($murid) {
                        $this->line("  - Deleting murid: {$murid->nama_lengkap}");
                        $murid->delete(); // Soft delete
                    }

                    // Delete user
                    $this->line("  - Deleting user: {$user->email}");
                    $user->delete(); // Soft delete
                }
            });

            $this->newLine();
            $this->info("✅ Successfully deleted {$testUsers->count()} test users!");
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
