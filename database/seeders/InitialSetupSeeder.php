<?php

namespace Database\Seeders;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class InitialSetupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissions & Roles
        $this->call(RoleAndPermissionSeeder::class);

        // 3. One Superadmin only
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@shineedu.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'Aktif',
                'email_verified_at' => now(),
            ]
        );
        $superadmin->syncRoles(['Superadmin']);

        $this->command->info('Database has been reset. Only Super Admin and basic rules remain.');
    }
}
