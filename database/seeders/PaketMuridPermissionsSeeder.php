<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaketMuridPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'enrollment.manage_paket' => 'Manage student packages and credits',
            // 'enrollment.view' already exists
        ];

        foreach ($permissions as $permission => $label) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign to Admin
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo('enrollment.manage_paket');
        }

        // Assign to Front Office
        $foRole = \Spatie\Permission\Models\Role::where('name', 'Front Office')->first();
        if ($foRole) {
            $foRole->givePermissionTo('enrollment.manage_paket');
        }
    }
}
