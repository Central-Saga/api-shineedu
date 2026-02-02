<?php

namespace App\Modules\Finance\Application\Services;

use App\Modules\Finance\Domain\Models\KasShift;
use Illuminate\Support\Facades\Auth;

class KasShiftService
{
    /**
     * Get the current open shift for the authenticated user
     */
    public function getCurrentShift(): ?KasShift
    {
        return KasShift::open()->latest('opened_at')->first();
    }

    /**
     * Check if there is an open shift
     */
    public function hasOpenShift(): bool
    {
        return KasShift::open()->exists();
    }

    /**
     * Open a new shift
     */
    public function openShift(float $openingBalance): KasShift
    {
        // Check if there's already an open shift
        if ($this->hasOpenShift()) {
            throw new \Exception('Sudah ada shift yang aktif. Tutup shift terlebih dahulu.');
        }

        $shift = KasShift::create([
            'opened_at' => now(),
            'opening_balance' => $openingBalance,
            'status' => KasShift::STATUS_OPEN,
            'opened_by' => Auth::id(),
        ]);

        // Notification: Foundation
        $user = Auth::user();
        $userName = $user ? $user->name : 'Unknown';
        $time = now()->format('d M Y H:i');
        $nominal = number_format($openingBalance, 0, ',', '.');

        $this->notifyFoundation(
            "Open Shift: {$userName}",
            "Shift kasir telah DIBUKA oleh {$userName} pada {$time}.\nSaldo Awal: Rp {$nominal}"
        );

        return $shift;
    }

    /**
     * Close a shift
     */
    public function closeShift(KasShift $shift, float $actualCash, ?string $notes = null): KasShift
    {
        if ($shift->isClosed()) {
            throw new \Exception('Shift sudah ditutup.');
        }

        $shift = $shift->close($actualCash, Auth::id(), $notes);

        // Notification: Foundation
        $user = Auth::user();
        $userName = $user ? $user->name : 'Unknown';
        $time = now()->format('d M Y H:i');
        $nominalActual = number_format($actualCash, 0, ',', '.');
        $nominalExpected = number_format($shift->expected_cash, 0, ',', '.');
        $difference = $shift->variance; // Assuming variance = actual - expected
        $nominalDiff = number_format($difference, 0, ',', '.');
        $statusDiff = $difference == 0 ? "PAS" : ($difference > 0 ? "LEBIH Rp {$nominalDiff}" : "KURANG Rp {$nominalDiff}");

        $this->notifyFoundation(
            "Close Shift: {$userName}",
            "Shift kasir telah DITUTUP oleh {$userName} pada {$time}.\n\n" .
                "Uang Fisik (Actual): Rp {$nominalActual}\n" .
                "Uang Sistem (Expected): Rp {$nominalExpected}\n" .
                "Selisih: {$statusDiff}\n" .
                "Catatan: {$notes}"
        );

        return $shift;
    }

    /**
     * Get shift summary
     */
    public function getShiftSummary(KasShift $shift): array
    {
        // Force reload to ensure we have latest transactions
        $shift->load(['transaksis', 'openedByUser', 'closedByUser']);
        $shift->refresh();
        $shift->load(['transaksis', 'openedByUser', 'closedByUser']);

        \Log::info('Shift Summary Debug', [
            'shift_id' => $shift->id,
            'transaksis_count' => $shift->transaksis->count(),
            'transaksis' => $shift->transaksis->toArray(),
        ]);

        $totals = $shift->calculateTotals();
        $methodSummary = $shift->getMethodSummary();
        $categorySummary = $shift->getCategorySummary();

        \Log::info('Method Summary', ['by_method' => $methodSummary]);

        return [
            'shift' => [
                'id' => $shift->id,
                'opened_at' => $shift->opened_at->format('Y-m-d H:i:s'),
                'closed_at' => $shift->closed_at?->format('Y-m-d H:i:s'),
                'status' => $shift->status,
                'opening_balance' => $shift->opening_balance,
                'closing_balance' => $shift->closing_balance,
                'expected_cash' => $shift->expected_cash,
                'actual_cash' => $shift->actual_cash,
                'variance' => $shift->variance,
                'notes' => $shift->notes,
                'opened_by' => $shift->openedByUser?->name,
                'closed_by' => $shift->closedByUser?->name,
            ],
            'totals' => $totals,
            'by_method' => $methodSummary,
            'by_category' => $categorySummary,
        ];
    }

    /**
     * Get daily summary
     */
    public function getDailySummary(?\Carbon\Carbon $date = null): array
    {
        $date = $date ?? now();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $shifts = KasShift::with('transaksis')
            ->whereBetween('opened_at', [$startOfDay, $endOfDay])
            ->get();

        $totalOpeningBalance = $shifts->sum('opening_balance');
        $totalIn = 0;
        $totalOut = 0;
        $byMethod = [];
        $byCategory = [];
        $transactionCount = 0;

        foreach ($shifts as $shift) {
            foreach ($shift->transaksis as $trx) {
                $transactionCount++;

                if ($trx->type === 'IN') {
                    $totalIn += $trx->amount;
                } else {
                    $totalOut += $trx->amount;
                }

                // Group by method
                if (!isset($byMethod[$trx->metode])) {
                    $byMethod[$trx->metode] = ['in' => 0, 'out' => 0];
                }
                $byMethod[$trx->metode][$trx->type === 'IN' ? 'in' : 'out'] += $trx->amount;

                // Group by category
                if (!isset($byCategory[$trx->kategori])) {
                    $byCategory[$trx->kategori] = ['in' => 0, 'out' => 0];
                }
                $byCategory[$trx->kategori][$trx->type === 'IN' ? 'in' : 'out'] += $trx->amount;
            }
        }

        return [
            'date' => $date->format('Y-m-d'),
            'shift_count' => $shifts->count(),
            'transaction_count' => $transactionCount,
            'opening_balance' => $totalOpeningBalance,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'net' => $totalIn - $totalOut,
            'by_method' => $byMethod,
            'by_category' => $byCategory,
            'shifts' => $shifts->map(fn($s) => [
                'id' => $s->id,
                'status' => $s->status,
                'opened_at' => $s->opened_at->format('H:i'),
                'closed_at' => $s->closed_at?->format('H:i'),
                'opening_balance' => $s->opening_balance,
                'closing_balance' => $s->closing_balance,
            ]),
        ];
    }
    protected function sendEmail(string $to, string $subject, string $message)
    {
        if (!empty($to)) {
            dispatch(new \App\Jobs\SendEmailJob($to, new \App\Mail\GeneralNotification($subject, $message)));
        }
    }

    protected function notifyFoundation(string $subject, string $message)
    {
        $foundationEmail = env('MAIL_TO_FOUNDATION');
        if ($foundationEmail) {
            $this->sendEmail($foundationEmail, $subject, $message);
        }
    }
}
