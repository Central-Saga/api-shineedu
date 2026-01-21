<?php

namespace Database\Seeders;

use App\Modules\HR\Models\Employee;
use App\Modules\Identity\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Link existing users to employees if they don't have one
        // Especially for Teacher and Admin roles
        $usersToLink = User::role(['Admin', 'Teacher'])->get();

        foreach ($usersToLink as $user) {
            $dob = '1990-01-01'; // Default for seeder
            $dateObj = new \DateTime($dob);
            $ddmmyy = $dateObj->format('dmy');
            $random = str_pad($user->id, 4, '0', STR_PAD_LEFT);

            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'kode_karyawan' => "{$ddmmyy}{$random}",
                    'kategori_karyawan' => 'tetap',
                    'status' => 'aktif',
                    'nomor_hp' => '08123456789',
                    'alamat' => 'Denpasar, Bali',
                    'tanggal_lahir' => $dob,
                ]
            );
        }

        // 2) Create some dummy employees with their users
        // Use factory for random ones
        Employee::factory()->count(10)->create();
    }
}
