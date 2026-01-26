<?php

namespace App\Modules\AcademicSessions\Application\Services;

use App\Modules\AcademicSessions\Domain\Models\Session;
use App\Modules\AcademicSessions\Domain\Models\SesiAbsensiMurid;
use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateSesiService
{
    public function generateForKelas($kelasId, $fromDate, $toDate, $sumber = 'SYSTEM', $autoPopulateAbsensi = true)
    {
        $start = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);

        // Get Active Jadwal for the Class
        $jadwals = JadwalKerja::where('kelas_id', $kelasId)
            ->where('status', 'Aktif')
            ->get();

        if ($jadwals->isEmpty()) {
            return [
                'success' => true,
                'message' => 'Tidak ada jadwal aktif untuk kelas ini.',
                'count' => 0
            ];
        }

        $createdCount = 0;

        DB::transaction(function () use ($start, $end, $jadwals, $kelasId, $sumber, $autoPopulateAbsensi, &$createdCount) {
            $period = \Carbon\CarbonPeriod::create($start, $end);

            // Pre-fetch active enrollments if auto-populate is on
            $anggotaIds = [];
            if ($autoPopulateAbsensi) {
                $anggotaIds = DB::table('kelas_enrollment')
                    ->where('kelas_id', $kelasId)
                    ->where('status_anggota', 'Aktif')
                    ->pluck('enrollment_id')
                    ->toArray();
            }

            foreach ($period as $date) {
                // Determine Hari (Senin, Selasa, etc.)
                // Carbon uses English names by default, but we need to match DB 'hari' column.
                // Assuming DB uses Indonesian names based on migration comment: `// Senin, Selasa, etc.`
                // We need a mapper.
                $hariIndo = $this->getHariIndo($date->dayOfWeek); // 0 (Sunday) - 6 (Saturday)

                foreach ($jadwals as $jadwal) {
                    if (strcasecmp($jadwal->hari, $hariIndo) === 0) {
                        // Found a matching schedule for this day
                        $existing = Session::where('jadwal_kerja_id', $jadwal->id)
                            ->whereDate('tanggal', $date)
                            ->exists();

                        if (!$existing) {
                            $session = Session::create([
                                'tanggal' => $date->format('Y-m-d'),
                                'jadwal_kerja_id' => $jadwal->id,
                                'kelas_id' => $kelasId,
                                'status' => 'TERJADWAL', // Backward compat
                                'status_sesi' => 'TERJADWAL',
                                'status_kehadiran_guru' => 'HADIR',
                                'sumber' => $sumber,
                                'guru_pengajar_id' => $jadwal->guru_pengajar_id,
                                'ruangan_kelas' => $jadwal->ruangan_kelas, // default rule
                            ]);

                            $createdCount++;

                            // Auto populate absensi
                            if ($autoPopulateAbsensi && !empty($anggotaIds)) {
                                foreach ($anggotaIds as $enrollmentId) {
                                    SesiAbsensiMurid::firstOrCreate(
                                        [
                                            'realisasi_jadwal_kerja_id' => $session->id,
                                            'enrollment_id' => $enrollmentId
                                        ],
                                        [
                                            'status' => 'HADIR'
                                        ]
                                    );
                                }
                            }
                        }
                    }
                }
            }
        });

        return [
            'success' => true,
            'message' => "Berhasil generate $createdCount sesi dari tanggal $fromDate s/d $toDate.",
            'count' => $createdCount
        ];
    }

    private function getHariIndo($dayOfWeek)
    {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];
        return $days[$dayOfWeek] ?? '';
    }
}
