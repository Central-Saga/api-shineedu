<?php

namespace Database\Seeders;

use App\Modules\HR\Domain\Models\PengaturanCutiRules;
use Illuminate\Database\Seeder;

class LeaveRulesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Group: Tetap / Full Time (100k)
        $fullTimeScenarios = [
            ['kategori' => 'tetap', 'subtipe' => null],
            ['kategori' => 'kontrak', 'subtipe' => 'Full Time'],
        ];

        $leaveTypes = ['cuti', 'izin', 'sakit'];

        foreach ($fullTimeScenarios as $scen) {
            foreach ($leaveTypes as $type) {
                PengaturanCutiRules::updateOrCreate(
                    [
                        'kategori_karyawan' => $scen['kategori'],
                        'subtipe_kontrak' => $scen['subtipe'],
                        'jenis' => $type,
                    ],
                    [
                        'potongan_tipe' => 'per_hari',
                        'potongan_nilai' => 100000,
                        'aktif' => true,
                    ]
                );
            }
        }

        // 2. Group: Part Time (50k)
        foreach ($leaveTypes as $type) {
            PengaturanCutiRules::updateOrCreate(
                [
                    'kategori_karyawan' => 'kontrak',
                    'subtipe_kontrak' => 'Part Time',
                    'jenis' => $type,
                ],
                [
                    'potongan_tipe' => 'per_hari',
                    'potongan_nilai' => 50000,
                    'aktif' => true,
                ]
            );
        }

        // 3. Group: Freelance
        foreach ($leaveTypes as $type) {
            PengaturanCutiRules::updateOrCreate(
                [
                    'kategori_karyawan' => 'freelance',
                    'jenis' => $type,
                ],
                [
                    'potongan_tipe' => 'per_hari',
                    'potongan_nilai' => 0,
                    'aktif' => true,
                ]
            );
        }

        $this->command->info('Success: Leave rules for tetap/FullTime (100k) and PartTime (50k) applied!');
    }
}
