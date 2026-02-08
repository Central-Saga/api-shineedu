<?php

namespace Database\Seeders;

use App\Modules\HR\Domain\Models\Employee;
use App\Modules\Academic\Domain\Models\Kelas;
use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use Illuminate\Database\Seeder;

class JadwalKerjaTestSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Employee::where('divisi', 'Teaching')->get();
        if ($teachers->isEmpty()) {
            $teachers = Employee::all();
        }

        if ($teachers->isEmpty()) {
            $this->command->error('No employees found to create schedules.');
            return;
        }

        $days = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
        $rooms = ["Ruang 1", "Ruang 2", "Ruang 3", "Ruang A", "Ruang B"];
        $subjects = ["Matematika", "Bahasa Inggris", "Coding Basic", "Coding Advanced", "Science"];
        $categories = ["coding", "non_coding"];

        for ($i = 0; $i < 20; $i++) {
            $startTime = rand(8, 16);
            $endTime = $startTime + rand(1, 2);

            JadwalKerja::create([
                'mata_pelajaran' => $subjects[array_rand($subjects)],
                'hari' => $days[array_rand($days)],
                'jam_mulai' => sprintf("%02d:00:00", $startTime),
                'jam_selesai' => sprintf("%02d:00:00", $endTime),
                'guru_pengajar_id' => $teachers->random()->id,
                'ruangan_kelas' => $rooms[array_rand($rooms)],
                'kategori' => $categories[array_rand($categories)],
                'status' => 'Aktif',
            ]);
        }

        $this->command->info('Successfully seeded 20 test schedules.');
    }
}
