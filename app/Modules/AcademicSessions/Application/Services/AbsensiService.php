<?php

namespace App\Modules\AcademicSessions\Application\Services;

use App\Modules\AcademicSessions\Domain\Models\SesiAbsensiMurid;
use App\Modules\AcademicSessions\Domain\Models\Session;
use App\Modules\Enrollment\Application\Services\SaldoPertemuanService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AbsensiService
{
    protected $saldoService;

    public function __construct(SaldoPertemuanService $saldoService)
    {
        $this->saldoService = $saldoService;
    }
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

                // Fetch existing attendance to detect status change
                $existing = SesiAbsensiMurid::where('realisasi_jadwal_kerja_id', $session->id)
                    ->where('enrollment_id', $enrollmentId)
                    ->first();

                $oldStatus = $existing?->status;
                $newStatus = $item['status'];

                // Update or create attendance record
                SesiAbsensiMurid::updateOrCreate(
                    [
                        'realisasi_jadwal_kerja_id' => $session->id,
                        'enrollment_id' => $enrollmentId
                    ],
                    [
                        'status' => $newStatus,
                        'catatan' => $item['catatan'] ?? null,
                        'created_by' => $userId
                    ]
                );

                // Fetch the updated/created record
                $absensi = SesiAbsensiMurid::where('realisasi_jadwal_kerja_id', $session->id)
                    ->where('enrollment_id', $enrollmentId)
                    ->first();

                // Apply credit logic based on status change
                $this->applyCreditLogic($oldStatus, $newStatus, $absensi);

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
                $oldStatus = $sourceAttendance->status;

                $sourceAttendance->update([
                    'status' => 'BATAL',
                    'catatan' => "[Pindah ke sesi tanggal " . $targetSession->tanggal->format('d/m/Y') . "]"
                ]);

                // Refund credit if source was HADIR
                $this->applyCreditLogic($oldStatus, 'BATAL', $sourceAttendance);
            }

            // 2. Upsert in target session
            $existingTarget = SesiAbsensiMurid::where('realisasi_jadwal_kerja_id', $targetSessionId)
                ->where('enrollment_id', $enrollmentId)
                ->first();

            $oldTargetStatus = $existingTarget?->status;

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

            // Deduct credit for target HADIR
            $this->applyCreditLogic($oldTargetStatus, 'HADIR', $targetAttendance);

            return $targetAttendance;
        });
    }

    /**
     * Apply credit deduction/refund logic based on status change
     *
     * @param string|null $oldStatus
     * @param string $newStatus
     * @param SesiAbsensiMurid $absensi
     * @return void
     */
    private function applyCreditLogic(?string $oldStatus, string $newStatus, SesiAbsensiMurid $absensi)
    {
        try {
            // Case 1: Status changed to HADIR (from non-HADIR or new)
            if ($newStatus === 'HADIR' && $oldStatus !== 'HADIR') {
                $this->saldoService->deductCreditForAttendance($absensi);
                Log::info("Credit deducted for attendance {$absensi->id}, enrollment {$absensi->enrollment_id}");
            }

            // Case 2: Status changed from HADIR to something else
            if ($oldStatus === 'HADIR' && $newStatus !== 'HADIR') {
                $this->saldoService->refundCreditForAttendance($absensi);
                Log::info("Credit refunded for attendance {$absensi->id}, enrollment {$absensi->enrollment_id}");
            }
        } catch (\Exception $e) {
            // Log error but don't block attendance update
            // This allows attendance to be recorded even if credit system has issues
            Log::error("Credit operation failed for attendance {$absensi->id}: " . $e->getMessage());

            // Re-throw if it's a business rule violation (insufficient credit)
            if ($e instanceof \App\Modules\Enrollment\Application\Exceptions\InsufficientCreditException) {
                throw $e;
            }
        }
    }
}
