<?php

namespace Database\Seeders;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Ensure roles exist
        $roles = ['Superadmin', 'Admin', 'Teacher', 'Student'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            // kalau kamu pakai guard 'api' atau 'sanctum', ganti guard_name sesuai config kamu
        }

        // 2) Create fixed users (easy to test/login)
        $superadmin = User::query()->firstOrCreate(
            ['email' => 'superadmin@shineedu.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'Aktif',
                'email_verified_at' => now(),
            ]
        );
        $superadmin->syncRoles(['Superadmin']);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@shineedu.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => 'Aktif',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['Admin']);

        $teacher = User::query()->firstOrCreate(
            ['email' => 'teacher@shineedu.test'],
            [
                'name' => 'Teacher',
                'password' => Hash::make('password'),
                'status' => 'Aktif',
                'email_verified_at' => now(),
            ]
        );
        $teacher->syncRoles(['Teacher']);

        $student = User::query()->firstOrCreate(
            ['email' => 'student@shineedu.test'],
            [
                'name' => 'Student',
                'password' => Hash::make('password'),
                'status' => 'Aktif',
                'email_verified_at' => now(),
            ]
        );
        $student->syncRoles(['Student']);

        // 3) Optional: generate extra dummy users (random)
        // Contoh: 10 student aktif + 2 student non aktif
        // User::factory()->count(10)->aktif()->withRole('Student')->create();
        // User::factory()->count(2)->nonAktif()->withRole('Student')->create();

        // Contoh: 3 teacher dummy
        // User::factory()->count(3)->aktif()->withRole('Teacher')->create();
    }
}
