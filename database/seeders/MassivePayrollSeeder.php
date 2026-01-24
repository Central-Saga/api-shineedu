<?php

namespace Database\Seeders;

use App\Modules\HR\Application\Services\PayrollService;
use App\Modules\HR\Domain\Models\Absensi;
use App\Modules\HR\Domain\Models\Cuti;
use App\Modules\HR\Domain\Models\Employee;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use App\Modules\Scheduling\Domain\Models\RealisasiJadwalKerja;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MassivePayrollSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Dummy Employees if count < 50
        $employeeCount = Employee::count();
        if ($employeeCount < 30) {
            $this->command->info("Creating dummy employees...");
            for ($i = 0; $i < 20; $i++) {
                $uniqueStr = Str::random(5);
                $name = "Employee " . $uniqueStr;
                $email = strtolower(Str::slug($name)) . $i . '@example.com';

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => Hash::make('password'),
                        'status' => 'Aktif',
                    ]
                );

                $kategori = ['Tetap', 'Kontrak', 'Freelance'][rand(0, 2)];
                $tipeGaji = $kategori === 'Freelance' ? 'per_sesi' : 'bulanan';

                Employee::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'kode_karyawan' => 'EMP-' . rand(100000, 999999),
                        'kategori_karyawan' => $kategori,
                        'tipe_gaji' => $tipeGaji,
                        'gaji_pokok' => $kategori === 'Freelance' ? 0 : rand(30, 80) * 100000,
                        'status' => 'aktif',
                        'subtipe_kontrak' => $kategori === 'Kontrak' ? 'Full Time' : null,
                    ]
                );
            }
        }

        $employees = Employee::all();
        $payrollService = app(PayrollService::class);
        $months = [
            ['month' => 11, 'year' => 2025],
            ['month' => 12, 'year' => 2025],
            ['month' => 1, 'year' => 2026],
        ];

        foreach ($months as $period) {
            $this->command->info("Generating activities and dummy payroll for {$period['month']}/{$period['year']}...");

            foreach ($employees as $emp) {
                // Determine random activity for this month
                $startDate = Carbon::create($period['year'], $period['month'], 1);
                $endDate = $startDate->copy()->endOfMonth();

                // A. Random Absensi
                for ($d = 1; $d <= $endDate->day; $d++) {
                    $date = Carbon::create($period['year'], $period['month'], $d);
                    if ($date->isWeekend()) continue;

                    // 90% attendance chance
                    if (rand(1, 100) <= 90) {
                        Absensi::updateOrCreate(
                            ['karyawan_id' => $emp->id, 'tanggal' => $date->toDateString()],
                            [
                                'status_kehadiran' => 'Hadir',
                                'jam_masuk' => $date->copy()->setTime(rand(7, 8), rand(0, 59), 0),
                                'jam_pulang' => $date->copy()->setTime(rand(16, 18), rand(0, 59), 0),
                                'sumber_absen' => 'Sistem',
                            ]
                        );
                    }
                }

                // B. Random Izin/Cuti (15% chance)
                if (rand(1, 100) <= 15) {
                    $leaveDate = $startDate->copy()->addDays(rand(5, 20));
                    Cuti::updateOrCreate(
                        ['karyawan_id' => $emp->id, 'start_date' => $leaveDate->toDateString()],
                        [
                            'end_date' => $leaveDate->toDateString(),
                            'jenis' => ['izin', 'sakit', 'cuti'][rand(0, 2)],
                            'status' => 'disetujui',
                            'keterangan' => 'Keperluan pribadi ' . rand(1, 10),
                            'potongan_tipe' => 'per_hari',
                            'potongan_nilai' => 50000,
                            'disetujui_oleh' => 1
                        ]
                    );
                }

                // C. Random Sessions for Freelancers
                if ($emp->tipe_gaji === 'per_sesi' || rand(1, 100) <= 30) {
                    $jadwal = JadwalKerja::firstOrCreate(
                        ['guru_pengajar_id' => $emp->id],
                        [
                            'mata_pelajaran' => 'Dummy Lesson',
                            'kategori' => 'general',
                            'hari' => 'Senin',
                            'jam_mulai' => '10:00:00',
                            'jam_selesai' => '12:00:00',
                            'tarif' => 100000,
                            'status' => 'Aktif',
                        ]
                    );

                    for ($s = 0; $s < 4; $s++) {
                        $sessionDate = $startDate->copy()->addDays(rand(1, 28));
                        RealisasiJadwalKerja::updateOrCreate(
                            ['jadwal_kerja_id' => $jadwal->id, 'tanggal' => $sessionDate->toDateString()],
                            [
                                'status' => 'disetujui',
                                'guru_pengajar_id' => $emp->id,
                                'sumber' => 'Sistem',
                                'disetujui_oleh' => 1
                            ]
                        );
                    }
                }
            }

            // D. AUTOMATICALLY SYNC PAYROLL for this period
            $payrollService->generateByMonth($period['month'], $period['year']);

            // Randomly mark some as PAID in previous months
            if ($period['year'] < 2026 || $period['month'] < 1) {
                \App\Modules\HR\Domain\Models\Payroll::where('bulan', $period['month'])
                    ->where('tahun', $period['year'])
                    ->update(['status' => 'paid', 'tanggal_pembayaran' => Carbon::create($period['year'], $period['month'], 28)]);
            }
        }

        $this->command->info("Success: Generated massive dummy database for Payroll!");
    }
}
