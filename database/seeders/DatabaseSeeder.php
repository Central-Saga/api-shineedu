<?php

namespace Database\Seeders;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            // EmployeeSeeder::class,
            KatalogSeeder::class,
            // RealisticBalineseSeeder::class,
        ]);
    }
}
