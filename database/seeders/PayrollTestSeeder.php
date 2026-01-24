<?php

namespace Database\Seeders;

use App\Modules\HR\Domain\Models\Absensi;
use App\Modules\HR\Domain\Models\Cuti;
use App\Modules\HR\Domain\Models\Employee;
use App\Modules\HR\Domain\Models\PengaturanCutiRules;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use App\Modules\Scheduling\Domain\Models\RealisasiJadwalKerja;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PayrollTestSeeder extends Seeder
{
    public function run(): void
    {
        // TARGET EXISTING EMPLOYEES from your screenshot
        $targets = [
            '0101900018' => ['name' => 'Latif Iswahyudi S.Ked', 'kategori' => 'Kontrak', 'subtipe' => 'Full Time', 'gaji' => 4500000],
            '0101900017' => ['name' => 'Ulva Shania Sudiati S.E.', 'kategori' => 'Tetap', 'subtipe' => null, 'gaji' => 5500000],
            '0101900003' => ['name' => 'Teacher', 'kategori' => 'Tetap', 'subtipe' => null, 'gaji' => 5000000],
        ];

        foreach ($targets as $kode => $info) {
            $emp = Employee::where('kode_karyawan', $kode)->first();
            if (!$emp) continue;

            // Update employee info to match test scenario
            $emp->update([
                'kategori_karyawan' => $info['kategori'],
                'subtipe_kontrak' => $info['subtipe'],
                'gaji_pokok' => $info['gaji'],
                'tipe_gaji' => 'bulanan',
            ]);

            // 1. Create Absensi (Jan 2026)
            for ($i = 1; $i <= 20; $i++) {
                $date = Carbon::create(2026, 1, $i);
                if ($date->isWeekend()) continue;

                Absensi::updateOrCreate(
                    ['karyawan_id' => $emp->id, 'tanggal' => $date->toDateString()],
                    [
                        'status_kehadiran' => 'Hadir',
                        'jam_masuk' => $date->copy()->setTime(8, 0, 0),
                        'jam_pulang' => $date->copy()->setTime(17, 0, 0),
                        'sumber_absen' => 'Sistem',
                    ]
                );
            }

            // 2. Create Cuti/Izin
            if ($kode === '0101900018') { // Latif: 1 day Izin
                Cuti::updateOrCreate(
                    ['karyawan_id' => $emp->id, 'start_date' => '2026-01-26'],
                    [
                        'end_date' => '2026-01-26',
                        'jenis' => 'izin',
                        'status' => 'disetujui',
                        'keterangan' => 'Urusan keluarga',
                        'potongan_tipe' => 'per_hari',
                        'potongan_nilai' => 50000,
                        'disetujui_oleh' => 1
                    ]
                );
            }

            if ($kode === '0101900017') { // Ulva: 2 days Sakit
                Cuti::updateOrCreate(
                    ['karyawan_id' => $emp->id, 'start_date' => '2026-01-15'],
                    [
                        'end_date' => '2026-01-16',
                        'jenis' => 'sakit',
                        'status' => 'disetujui',
                        'keterangan' => 'Demam',
                        'potongan_tipe' => 'none',
                        'potongan_nilai' => 0,
                        'disetujui_oleh' => 1
                    ]
                );
            }
        }

        // Add Freelancer scenario (Cici Maryati 1907851982)
        $empFree = Employee::where('kode_karyawan', '1907851982')->first();
        if ($empFree) {
            $empFree->update(['kategori_karyawan' => 'Freelance', 'tipe_gaji' => 'per_sesi', 'gaji_pokok' => 0]);

            $jadwal = JadwalKerja::updateOrCreate(
                ['guru_pengajar_id' => $empFree->id, 'mata_pelajaran' => 'Coding Python'],
                [
                    'kategori' => 'coding',
                    'hari' => 'Senin',
                    'jam_mulai' => '15:00:00',
                    'jam_selesai' => '17:00:00',
                    'tarif' => 150000,
                    'status' => 'Aktif',
                ]
            );

            for ($w = 0; $w < 4; $w++) {
                $date = Carbon::create(2026, 1, 5)->addWeeks($w);
                if ($date->month !== 1) continue;
                RealisasiJadwalKerja::updateOrCreate(
                    ['jadwal_kerja_id' => $jadwal->id, 'tanggal' => $date->toDateString()],
                    [
                        'status' => 'disetujui',
                        'guru_pengajar_id' => $empFree->id,
                        'sumber' => 'Sistem',
                        'disetujui_oleh' => 1
                    ]
                );
            }
        }

        $this->command->info('Success: Data for existing employees (Jan 2026) generated!');
    }
}
