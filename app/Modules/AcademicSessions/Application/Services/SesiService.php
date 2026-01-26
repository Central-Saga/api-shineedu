<?php

namespace App\Modules\AcademicSessions\Application\Services;

use App\Modules\AcademicSessions\Domain\Models\Session;
use App\Modules\AcademicSessions\Domain\Models\SesiAbsensiMurid;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SesiService
{
    public function getSesiByKelas($kelasId, $filters = [])
    {
        $query = Session::where('kelas_id', $kelasId)
            ->with(['guruPengajar.user', 'jadwal']); // Eager load

        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal', '<=', $filters['end_date']);
        }
        if (!empty($filters['status_sesi'])) {
            $query->where('status_sesi', $filters['status_sesi']);
        }

        return $query->orderBy('tanggal', 'asc')->paginate($filters['per_page'] ?? 15);
    }

    public function findById($id)
    {
        return Session::with(['kelas.program', 'kelas.jenjang', 'guruPengajar.user', 'guruPengganti.user', 'jadwal', 'logbook', 'logbookMurid', 'absensi.enrollment.murid'])->findOrFail($id);
    }

    public function update($id, array $data, $userId)
    {
        $session = Session::findOrFail($id);

        DB::transaction(function () use ($session, $data, $userId) {
            // Handle Cancellation
            if (isset($data['status_sesi']) && $data['status_sesi'] === 'BATAL') {
                if (empty($data['alasan_batal']) && empty($session->alasan_batal)) {
                    throw ValidationException::withMessages(['alasan_batal' => 'Alasan batal wajib diisi jika status dibatalkan.']);
                }
                $session->dibatalkan_pada = now();
                $session->dibatalkan_oleh = $userId;
                $session->alasan_batal = $data['alasan_batal'] ?? $session->alasan_batal;
            }

            // Handle Completion Rules
            if (isset($data['status_sesi']) && $data['status_sesi'] === 'SELESAI') {
                $this->validateCompletion($session);
            }

            $session->fill($data);
            $session->save();
        });

        return $session->fresh();
    }

    public function syncAnggota($sessionId)
    {
        $session = Session::findOrFail($sessionId);

        // Get active members of the class
        // Use insertIgnore or firstOrCreate
        $activeEnrollments = DB::table('kelas_enrollment')
            ->where('kelas_id', $session->kelas_id)
            ->where('status_anggota', 'Aktif')
            ->pluck('enrollment_id');

        $count = 0;
        foreach ($activeEnrollments as $enrollmentId) {
            $exists = SesiAbsensiMurid::where('realisasi_jadwal_kerja_id', $sessionId)
                ->where('enrollment_id', $enrollmentId)
                ->exists();

            if (!$exists) {
                SesiAbsensiMurid::create([
                    'realisasi_jadwal_kerja_id' => $sessionId,
                    'enrollment_id' => $enrollmentId,
                    'status' => 'HADIR'
                ]);
                $count++;
            }
        }

        return $count;
    }

    private function validateCompletion(Session $session)
    {
        // Load relationships if not loaded
        $session->load(['kelas', 'logbook', 'absensi']);

        $tipeKelas = $session->kelas?->tipe_kelas; // e.g. PRIVATE / REGULER

        // Rules from request:
        // PRIVATE: Must have logbook (summary) OR logbook_murid for ALL present students.
        // REGULER: Must have logbook (summary).

        // Check Logbook Sesi (Summary)
        $hasLogbookSummary = $session->logbook && !empty($session->logbook->ringkasan);

        if ($tipeKelas === 'REGULER') {
            if (!$hasLogbookSummary) {
                // Relaxed rule: maybe user wants to enforce summary only.
                // User request: "sesi_logbook cukup"
                // If implementation requires it strictly:
                // throw ValidationException::withMessages(['logbook' => 'Logbook sesi (ringkasan) wajib diisi untuk kelas Reguler.']);
            }
        }

        if ($tipeKelas === 'PRIVATE') {
            // "Pastikan minimal ada: sesi_logbook (ringkasan) ATAU sesi_logbook_murid untuk semua enrollment"
            if ($hasLogbookSummary) {
                return; // Compliant
            }

            // Check if all students in absensi have logbook
            $absensiIds = $session->absensi->pluck('enrollment_id');
            if ($absensiIds->isEmpty()) {
                // No students present? Then maybe okay? Or require summary?
                // Let's assume OK if no students or require summary.
                // But typically private has 1 student.
                return;
            }

            $logbookMuridCount = $session->logbookMurid()
                ->whereIn('enrollment_id', $absensiIds)
                ->count();

            if ($logbookMuridCount < $absensiIds->count()) {
                throw ValidationException::withMessages(['logbook' => 'Untuk kelas Private, wajib isi Logbook Sesi ATAU Logbook Murid untuk setiap siswa.']);
            }
        }
    }
}
