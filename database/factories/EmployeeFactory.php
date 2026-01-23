<?php

namespace Database\Factories;

use App\Modules\HR\Domain\Models\Employee;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\HR\Domain\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $birthDate = fake()->date();
        $dateObj = new \DateTime($birthDate);
        $ddmmyy = $dateObj->format('dmy');
        $random = fake()->numberBetween(1000, 9999);

        return [
            'user_id' => User::factory(),
            'kode_karyawan' => "{$ddmmyy}{$random}",
            'kategori_karyawan' => fake()->randomElement(['tetap', 'kontrak', 'freelance']),
            'subtipe_kontrak' => fake()->randomElement(['full_time', 'part_time']),
            'tipe_gaji' => fake()->randomElement(['bulanan', 'per_sesi']),
            'gaji_pokok' => fake()->numberBetween(3000000, 10000000),
            'bank_nama' => fake()->randomElement(['BCA', 'BNI', 'Mandiri', 'BRI']),
            'bank_no_rekening' => fake()->bankAccountNumber(),
            'nomor_hp' => fake()->phoneNumber(),
            'alamat' => fake()->address(),
            'tanggal_lahir' => $birthDate,
            'divisi' => fake()->randomElement(['Coding', 'Non-Coding', 'Operasional']),
            'status' => 'aktif',
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Employee $employee) {
            $user = $employee->user;
            if ($user && !$user->hasAnyRole(\Spatie\Permission\Models\Role::all())) {
                $user->assignRole('Teacher');
            }
        });
    }
}
