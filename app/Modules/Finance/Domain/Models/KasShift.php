<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KasShift extends Model
{
    public const STATUS_OPEN = 'OPEN';
    public const STATUS_CLOSED = 'CLOSED';

    protected $table = 'kas_shift';

    protected $fillable = [
        'opened_at',
        'closed_at',
        'status',
        'opening_balance',
        'closing_balance',
        'expected_cash',
        'actual_cash',
        'variance',
        'notes',
        'opened_by',
        'closed_by',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'variance' => 'decimal:2',
    ];

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function transaksis(): HasMany
    {
        return $this->hasMany(KasTransaksi::class, 'shift_id');
    }

    public function openedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('opened_at', now()->toDateString());
    }

    // ─────────────────────────────────────────────────────────────
    // COMPUTED ATTRIBUTES
    // ─────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    // ─────────────────────────────────────────────────────────────
    // METHODS
    // ─────────────────────────────────────────────────────────────

    /**
     * Calculate totals from linked transactions
     */
    public function calculateTotals(): array
    {
        $transactions = $this->transaksis;

        $totalIn = $transactions->where('type', KasTransaksi::TYPE_IN)->sum('amount');
        $totalOut = $transactions->where('type', KasTransaksi::TYPE_OUT)->sum('amount');

        // Cash transactions only
        $cashIn = $transactions
            ->where('type', KasTransaksi::TYPE_IN)
            ->where('metode', KasTransaksi::METODE_CASH)
            ->sum('amount');
        $cashOut = $transactions
            ->where('type', KasTransaksi::TYPE_OUT)
            ->where('metode', KasTransaksi::METODE_CASH)
            ->sum('amount');

        $expectedCash = $this->opening_balance + $cashIn - $cashOut;

        return [
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'net' => $totalIn - $totalOut,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'expected_cash' => $expectedCash,
            'transaction_count' => $transactions->count(),
        ];
    }

    /**
     * Get summary grouped by payment method
     */
    public function getMethodSummary(): array
    {
        $transactions = $this->transaksis;
        $methods = [];

        foreach ($transactions->groupBy('metode') as $metode => $trxs) {
            $methods[$metode] = [
                'in' => $trxs->where('type', KasTransaksi::TYPE_IN)->sum('amount'),
                'out' => $trxs->where('type', KasTransaksi::TYPE_OUT)->sum('amount'),
            ];
        }

        return $methods;
    }

    /**
     * Get summary grouped by category
     */
    public function getCategorySummary(): array
    {
        $transactions = $this->transaksis;
        $categories = [];

        foreach ($transactions->groupBy('kategori') as $kategori => $trxs) {
            $categories[$kategori] = [
                'in' => $trxs->where('type', KasTransaksi::TYPE_IN)->sum('amount'),
                'out' => $trxs->where('type', KasTransaksi::TYPE_OUT)->sum('amount'),
            ];
        }

        return $categories;
    }

    /**
     * Close this shift
     */
    public function close(float $actualCash, int $closedBy, ?string $notes = null): self
    {
        $totals = $this->calculateTotals();

        $this->closed_at = now();
        $this->status = self::STATUS_CLOSED;
        $this->closing_balance = $totals['expected_cash'];
        $this->expected_cash = $totals['expected_cash'];
        $this->actual_cash = $actualCash;
        $this->variance = $actualCash - $totals['expected_cash'];
        $this->notes = $notes;
        $this->closed_by = $closedBy;
        $this->save();

        return $this;
    }
}
