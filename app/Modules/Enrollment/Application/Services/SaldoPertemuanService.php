<?php

namespace App\Modules\Enrollment\Application\Services;

use App\Modules\AcademicSessions\Domain\Models\SesiAbsensiMurid;
use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Enrollment\Application\Exceptions\InsufficientCreditException;
use App\Modules\Enrollment\Application\Exceptions\NoActivePaketException;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Enrollment\Domain\Models\PaketMurid;
use App\Modules\Enrollment\Domain\Models\PaketMuridLedger;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaldoPertemuanService
{
    /**
     * Create a new student package and initialize with TOPUP ledger
     *
     * @param Enrollment $enrollment
     * @param int $paketId
     * @param array $data Additional data (tanggal_mulai, catatan)
     * @return PaketMurid
     */
    public function createPaketMurid(Enrollment $enrollment, int $paketId, array $data = [])
    {
        return DB::transaction(function () use ($enrollment, $paketId, $data) {
            // Get master paket to read pertemuan_per_bulan
            $paket = Paket::findOrFail($paketId);

            // Create paket_murid record
            $paketMurid = PaketMurid::create([
                'enrollment_id' => $enrollment->id,
                'paket_id' => $paketId,
                'status' => 'AKTIF',
                'tanggal_mulai' => $data['tanggal_mulai'] ?? now()->toDateString(),
                'tanggal_berakhir' => $data['tanggal_berakhir'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create TOPUP ledger entry
            $this->createLedgerEntry(
                paketMurid: $paketMurid,
                type: PaketMuridLedger::TYPE_TOPUP,
                qty: $paket->pertemuan_per_bulan ?? 0,
                referenceType: PaketMuridLedger::REF_PURCHASE,
                referenceId: null,
                reason: 'Pembelian paket ' . $paket->nama,
                userId: auth()->id()
            );

            // Load relationships for response
            $paketMurid->load(['paket', 'ledger']);

            return $paketMurid;
        });
    }

    /**
     * Deduct credit when attendance is marked as HADIR
     *
     * @param SesiAbsensiMurid $absensi
     * @return PaketMuridLedger
     * @throws InsufficientCreditException
     * @throws NoActivePaketException
     */
    public function deductCreditForAttendance(SesiAbsensiMurid $absensi)
    {
        return DB::transaction(function () use ($absensi) {
            // Check if already deducted (idempotency)
            $existing = PaketMuridLedger::where('reference_type', PaketMuridLedger::REF_ATTENDANCE)
                ->where('reference_id', $absensi->id)
                ->where('type', PaketMuridLedger::TYPE_USE)
                ->first();

            if ($existing) {
                Log::info("Credit already deducted for attendance {$absensi->id}, skipping");
                return $existing;
            }

            // Select package using FIFO strategy
            $paketMurid = $this->selectPackageForDeduction($absensi->enrollment);

            if (!$paketMurid) {
                throw new NoActivePaketException(
                    "Tidak ada paket aktif dengan saldo untuk enrollment {$absensi->enrollment_id}"
                );
            }

            // Check if saldo > 0
            $currentSaldo = $paketMurid->saldo_current;
            if ($currentSaldo <= 0) {
                throw new InsufficientCreditException(
                    "Saldo pertemuan habis. Silakan beli paket baru atau hubungi admin."
                );
            }

            // Create USE ledger entry
            return $this->createLedgerEntry(
                paketMurid: $paketMurid,
                type: PaketMuridLedger::TYPE_USE,
                qty: -1,
                referenceType: PaketMuridLedger::REF_ATTENDANCE,
                referenceId: $absensi->id,
                reason: "Hadir di sesi tanggal " . ($absensi->session->tanggal ?? 'N/A'),
                userId: auth()->id()
            );
        });
    }

    /**
     * Refund credit when HADIR attendance is cancelled/changed
     *
     * @param SesiAbsensiMurid $absensi
     * @return PaketMuridLedger|null
     */
    public function refundCreditForAttendance(SesiAbsensiMurid $absensi)
    {
        return DB::transaction(function () use ($absensi) {
            // Find the USE ledger entry for this attendance
            $useLedger = PaketMuridLedger::where('reference_type', PaketMuridLedger::REF_ATTENDANCE)
                ->where('reference_id', $absensi->id)
                ->where('type', PaketMuridLedger::TYPE_USE)
                ->first();

            if (!$useLedger) {
                Log::info("No USE ledger found for attendance {$absensi->id}, nothing to refund");
                return null;
            }

            // Check if already refunded (idempotency)
            $existingRefund = PaketMuridLedger::where('reference_type', PaketMuridLedger::REF_ATTENDANCE_ROLLBACK)
                ->where('reference_id', $absensi->id)
                ->where('type', PaketMuridLedger::TYPE_ADJUST)
                ->first();

            if ($existingRefund) {
                Log::info("Credit already refunded for attendance {$absensi->id}, skipping");
                return $existingRefund;
            }

            // Create ADJUST ledger entry to refund
            return $this->createLedgerEntry(
                paketMurid: $useLedger->paketMurid,
                type: PaketMuridLedger::TYPE_ADJUST,
                qty: 1,
                referenceType: PaketMuridLedger::REF_ATTENDANCE_ROLLBACK,
                referenceId: $absensi->id,
                reason: "Pembatalan kehadiran (status: {$absensi->status})",
                userId: auth()->id()
            );
        });
    }

    /**
     * Admin manual adjustment (expire, compensate, etc.)
     *
     * @param PaketMurid $paketMurid
     * @param string $type ADJUST or EXPIRE
     * @param int $qty Positive or negative
     * @param string $reason Required reason
     * @param User|null $user
     * @return array
     */
    public function adminAdjust(PaketMurid $paketMurid, string $type, int $qty, string $reason, ?User $user = null)
    {
        return DB::transaction(function () use ($paketMurid, $type, $qty, $reason, $user) {
            // Validate type
            if (!in_array($type, [PaketMuridLedger::TYPE_ADJUST, PaketMuridLedger::TYPE_EXPIRE])) {
                throw new \InvalidArgumentException("Invalid adjustment type: {$type}");
            }

            // Create ledger entry
            $ledger = $this->createLedgerEntry(
                paketMurid: $paketMurid,
                type: $type,
                qty: $qty,
                referenceType: PaketMuridLedger::REF_ADMIN_ADJUST,
                referenceId: null,
                reason: $reason,
                userId: $user?->id ?? auth()->id()
            );

            // Reload to get updated saldo
            $paketMurid->refresh();

            return [
                'paket_murid' => $paketMurid,
                'ledger' => $ledger,
                'saldo_current' => $paketMurid->saldo_current,
            ];
        });
    }

    /**
     * Get saldo summary for an enrollment
     *
     * @param Enrollment $enrollment
     * @return array
     */
    public function getSaldoByEnrollment(Enrollment $enrollment)
    {
        $paketMurids = PaketMurid::where('enrollment_id', $enrollment->id)
            ->active()
            ->with(['paket', 'ledger'])
            ->get();

        return $paketMurids->map(function ($pm) {
            return [
                'paket_murid_id' => $pm->id,
                'paket_id' => $pm->paket_id,
                'paket_nama' => $pm->paket->nama ?? 'N/A',
                'status' => $pm->status,
                'saldo_current' => $pm->saldo_current,
                'total_topup' => $pm->total_topup,
                'total_use' => $pm->total_use,
                'tanggal_mulai' => $pm->tanggal_mulai?->format('Y-m-d'),
                'tanggal_berakhir' => $pm->tanggal_berakhir?->format('Y-m-d'),
                'last_activity_at' => $pm->ledger->max('created_at')?->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Select package for deduction using FIFO strategy
     *
     * @param Enrollment $enrollment
     * @return PaketMurid|null
     */
    public function selectPackageForDeduction(Enrollment $enrollment)
    {
        // FIFO: Select oldest active package with positive balance
        return PaketMurid::where('enrollment_id', $enrollment->id)
            ->active()
            ->with('ledger')
            ->get()
            ->filter(function ($pm) {
                return $pm->saldo_current > 0;
            })
            ->sortBy([
                ['tanggal_mulai', 'asc'],
                ['id', 'asc'],
            ])
            ->first();
    }

    /**
     * Create a ledger entry (helper method)
     *
     * @param PaketMurid $paketMurid
     * @param string $type
     * @param int $qty
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $reason
     * @param int|null $userId
     * @return PaketMuridLedger
     */
    private function createLedgerEntry(
        PaketMurid $paketMurid,
        string $type,
        int $qty,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reason = null,
        ?int $userId = null
    ): PaketMuridLedger {
        return PaketMuridLedger::create([
            'paket_murid_id' => $paketMurid->id,
            'tanggal' => now(),
            'type' => $type,
            'qty' => $qty,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reason' => $reason,
            'created_by' => $userId,
        ]);
    }
}
