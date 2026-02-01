<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class KasTransaksi extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'kas_transaksi';

    protected $fillable = [
        'receipt_number',
        'tanggal',
        'type',
        'amount',
        'metode',
        'kategori',
        'keterangan',
        'payment_details',
        'pihak',
        'reference_type',
        'reference_id',
        'external_ref',
        'idempotency_key',
        'shift_id', // Link to shift
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Register media collections for payment proof
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proof')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
            ->singleFile(); // Only one proof per transaction
    }

    /**
     * Register media conversions (optional)
     */
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->nonQueued();
    }

    // Type constants
    const TYPE_IN = 'IN';
    const TYPE_OUT = 'OUT';

    // Payment method constants
    const METODE_CASH = 'CASH';
    const METODE_TRANSFER = 'TRANSFER';
    const METODE_QRIS = 'QRIS';
    const METODE_E_WALLET = 'E_WALLET';
    const METODE_OTHER = 'OTHER';

    // Reference type constants
    const REF_ENROLLMENT_FEE = 'enrollment_fee';
    const REF_PAKET_TOPUP = 'paket_topup';
    const REF_MANUAL = 'manual';

    // Common kategori for IN
    const KATEGORI_PEMBAYARAN_PAKET = 'Pembayaran Paket';
    const KATEGORI_BIAYA_PENDAFTARAN = 'Biaya Pendaftaran';
    const KATEGORI_LAINNYA_IN = 'Lainnya';

    // Common kategori for OUT
    const KATEGORI_GAJI = 'Gaji';
    const KATEGORI_SEWA = 'Sewa';
    const KATEGORI_ATK = 'ATK';
    const KATEGORI_OPERASIONAL = 'Operasional';
    const KATEGORI_REFUND = 'Refund';
    const KATEGORI_LAINNYA_OUT = 'Lainnya';

    // Relationships

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paketTopup()
    {
        return $this->belongsTo(\App\Modules\Enrollment\Domain\Models\PaketMurid::class, 'reference_id');
    }

    public function shift()
    {
        return $this->belongsTo(KasShift::class, 'shift_id');
    }

    // Scopes

    public function scopeIncome($query)
    {
        return $query->where('type', self::TYPE_IN);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', self::TYPE_OUT);
    }

    public function scopeByKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('tanggal', '>=', $from);
        }
        if ($to) {
            $query->where('tanggal', '<=', $to);
        }
        return $query;
    }

    /**
     * Find by idempotency key (for duplicate prevention)
     */
    public static function findByIdempotencyKey(?string $key): ?self
    {
        if (!$key) {
            return null;
        }
        return static::where('idempotency_key', $key)->first();
    }

    /**
     * Generate unique receipt number
     * Format: KWT-YYMM-XXXXX (e.g., KWT-2601-A3F2K)
     */
    public static function generateReceiptNumber(): string
    {
        $prefix = 'KWT';
        $yearMonth = now()->format('ym'); // e.g., 2601 for Jan 2026

        do {
            // Generate random 5-character alphanumeric string (uppercase)
            $random = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5));
            $receiptNumber = "{$prefix}-{$yearMonth}-{$random}";

            // Check if exists
            $exists = static::where('receipt_number', $receiptNumber)->exists();
        } while ($exists);

        return $receiptNumber;
    }
}
