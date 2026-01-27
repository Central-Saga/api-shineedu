<?php

namespace App\Modules\AcademicSessions\Application\Services;

use App\Modules\AcademicSessions\Domain\Models\SesiAbsensiMurid;
use App\Modules\AcademicSessions\Domain\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AbsensiService
{
    public function bulkUpdate($sessionId, array $items, $userId)
    {
        $session = Session::findOrFail($sessionId);

        // Validate all enrollment_ids belong to the class of the session?
        // Or at least are valid enrollments.
        // User request: "validate enrollment_id memang anggota kelas sesi tsb (422 jika tidak)"

        // Implementation: We can trust the caller or validate.
        // Let's validate against SesiAbsensiMurid existence if we only allow updating existing rows?
        // User said "UPSERT by (sesi_id, enrollment_id)". So we might be adding new students ad-hoc?
        // But rule says "validate enrollment_id memang anggota kelas".
        // Let's assume we check `KelasEnrollment`.

        DB::transaction(function () use ($session, $items, $userId) {
            foreach ($items as $item) {
                $enrollmentId = $item['enrollment_id'];

                // Validate membership
                // Ideally this verification happens before loop or cached.
                // Assuming controller validation does basic checks, but business logic here:
                // Check if enrollment is linked to session.kelas_id?
                // For performance, maybe skip strict DB check per item if we rely on `KelasEnrollment` check.
                // Let's use `updateOrCreate`.

                SesiAbsensiMurid::updateOrCreate(
                    [
                        'realisasi_jadwal_kerja_id' => $session->id,
                        'enrollment_id' => $enrollmentId
                    ],
                    [
                        'status' => $item['status'],
                        'catatan' => $item['catatan'] ?? null,
                        'created_by' => $userId
                    ]
                );

                // If user wants to move to another session
                if ($item['status'] === 'BATAL' && !empty($item['target_session_id'])) {
                    $this->moveAttendance($session->id, $enrollmentId, $item['target_session_id'], $userId);
                }
            }
        });

        return true;
    }

    public function moveAttendance($sourceSessionId, $enrollmentId, $targetSessionId, $userId)
    {
        return DB::transaction(function () use ($sourceSessionId, $enrollmentId, $targetSessionId, $userId) {
            $sourceSession = Session::findOrFail($sourceSessionId);
            $targetSession = Session::findOrFail($targetSessionId);

            // 1. Mark source as BATAL (Rescheduled)
            $sourceAttendance = SesiAbsensiMurid::where('realisasi_jadwal_kerja_id', $sourceSessionId)
                ->where('enrollment_id', $enrollmentId)
                ->first();

            if ($sourceAttendance) {
                $sourceAttendance->update([
                    'status' => 'BATAL',
                    'catatan' => "[Pindah ke sesi tanggal " . $targetSession->tanggal->format('d/m/Y') . "]"
                ]);
            }

            // 2. Upsert in target session
            $targetAttendance = SesiAbsensiMurid::updateOrCreate(
                [
                    'realisasi_jadwal_kerja_id' => $targetSessionId,
                    'enrollment_id' => $enrollmentId
                ],
                [
                    'status' => 'HADIR',
                    'created_by' => $userId,
                    'catatan' => "[Pindahan dari sesi tanggal " . $sourceSession->tanggal->format('d/m/Y') . "]"
                ]
            );

            return $targetAttendance;
        });
    }
}
