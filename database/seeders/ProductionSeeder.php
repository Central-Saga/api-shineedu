<?php

namespace Database\Seeders;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for Production.
     */
    public function run(): void
    {
        // 1. Permissions & Roles
        $this->call(RoleAndPermissionSeeder::class);

        // 2. Catalog Data (Jenjang, Program, Paket, Harga)
        $this->call(KatalogSeeder::class);

        // 3. HR Rules (Leave Rules, etc. - No Employees)
        $this->call(LeaveRulesSeeder::class);

        // 4. Create Only Super Admin and Admin

        // Super Admin
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

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@shineedu.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => 'Aktif',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['Admin']);

        $this->command->info('Production Seeding Completed.');
        $this->command->info('- Roles & Permissions seeded.');
        $this->command->info('- Catalog seeded.');
        $this->command->info('- Leave Rules seeded.');
        $this->command->info('- Users created: Super Admin & Admin only.');
        $this->command->info('- No employees, no dummy students/teachers.');
    }
}
