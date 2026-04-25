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

    /**
     * Bulk update absensi dengan saldo handling yang fleksibel
     * 
     * @param int $sessionId
     * @param array $items
     * @param int $userId
     * @return array ['success' => true, 'warnings' => [...]]
     */
    public function bulkUpdate($sessionId, array $items, $userId)
    {
        $session = Session::findOrFail($sessionId);
        $warnings = [];

        DB::transaction(function () use ($session, $items, $userId, &$warnings) {
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

                // Fetch the updated/created record with enrollment
                $absensi = SesiAbsensiMurid::where('realisasi_jadwal_kerja_id', $session->id)
                    ->where('enrollment_id', $enrollmentId)
                    ->first();

                // Apply credit logic with flexible handling
                $result = $this->applyCreditLogic($oldStatus, $newStatus, $absensi);
                
                if ($result['warning']) {
                    $warnings[] = $result['warning'];
                }

                // If user wants to move to another session
                if ($item['status'] === 'BATAL' && !empty($item['target_session_id'])) {
                    $this->moveAttendance($session->id, $enrollmentId, $item['target_session_id'], $userId);
                }
            }
        });

        return [
            'success' => true,
            'warnings' => $warnings
        ];
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
     * Dengan handling fleksibel untuk enrollment dari i-seller:
     * - saldo_override = -1: unlimited (tidak dicek)
     * - saldo_override >= 0: pakai saldo manual
     * - saldo_override = null: pakai sistem ledger normal
     *
     * @param string|null $oldStatus
     * @param string $newStatus
     * @param SesiAbsensiMurid $absensi
     * @return array ['success' => bool, 'warning' => string|null]
     */
    private function applyCreditLogic(?string $oldStatus, string $newStatus, SesiAbsensiMurid $absensi): array
    {
        $warning = null;
        
        try {
            // Load enrollment dengan relasi
            $enrollment = $absensi->enrollment;
            
            if (!$enrollment) {
                Log::warning("Enrollment not found for attendance {$absensi->id}");
                return ['success' => true, 'warning' => null];
            }

            // Cek saldo override
            $saldoOverride = $enrollment->getSaldoOverride();
            
            // Case 1: Unlimited (-1) - tidak dicek saldo sama sekali
            if ($saldoOverride === -1) {
                Log::info("Unlimited saldo for enrollment {$enrollment->id}, skipping credit check");
                return ['success' => true, 'warning' => null];
            }
            
            // Case 2: Saldo manual (0 atau positif)
            if ($saldoOverride !== null && $saldoOverride >= 0) {
                // Update saldo override (decrement)
                if ($newStatus === 'HADIR' && $oldStatus !== 'HADIR') {
                    if ($saldoOverride > 0) {
                        $enrollment->update(['saldo_override' => $saldoOverride - 1]);
                        Log::info("Manual saldo decremented for enrollment {$enrollment->id}: {$saldoOverride} -> " . ($saldoOverride - 1));
                    } else {
                        // Saldo 0, tetap izinkan tapi kasih warning
                        $warning = "Murid {$enrollment->murid?->nama_lengkap} (Enrollment: {$enrollment->kode_enrollment}) hadir dengan saldo 0";
                        Log::warning($warning);
                    }
                }
                
                // Refund jika dari HADIR ke status lain
                if ($oldStatus === 'HADIR' && $newStatus !== 'HADIR' && $saldoOverride >= 0) {
                    $enrollment->update(['saldo_override' => $saldoOverride + 1]);
                    Log::info("Manual saldo refunded for enrollment {$enrollment->id}: {$saldoOverride} -> " . ($saldoOverride + 1));
                }
                
                return ['success' => true, 'warning' => $warning];
            }

            // Case 3: Sistem ledger normal (saldo_override = null)
            // Case 1: Status IS HADIR. The service handles idempotency (won't deduct twice).
            if ($newStatus === 'HADIR') {
                $this->saldoService->deductCreditForAttendance($absensi);
            }

            // Case 2: Status changed FROM HADIR to something else
            if ($oldStatus === 'HADIR' && $newStatus !== 'HADIR') {
                $this->saldoService->refundCreditForAttendance($absensi);
            }
            
            return ['success' => true, 'warning' => null];
            
        } catch (\App\Modules\Enrollment\Application\Exceptions\InsufficientCreditException $e) {
            // Saldo habis tapi tetap izinkan absensi, kasih warning saja
            $warning = "Saldo pertemuan habis untuk murid {$absensi->enrollment?->murid?->nama_lengkap} (Enrollment: {$absensi->enrollment?->kode_enrollment})";
            Log::warning($warning . ": " . $e->getMessage());
            return ['success' => true, 'warning' => $warning];
            
        } catch (\App\Modules\Enrollment\Application\Exceptions\NoActivePaketException $e) {
            // Tidak ada paket aktif tapi tetap izinkan absensi
            $warning = "Tidak ada paket aktif untuk murid {$absensi->enrollment?->murid?->nama_lengkap} (Enrollment: {$absensi->enrollment?->kode_enrollment})";
            Log::warning($warning . ": " . $e->getMessage());
            return ['success' => true, 'warning' => $warning];
            
        } catch (\Exception $e) {
            // Log error tapi jangan block absensi
            Log::error("Credit operation failed for attendance {$absensi->id}: " . $e->getMessage());
            return ['success' => true, 'warning' => null];
        }
    }
}
