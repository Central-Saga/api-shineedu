<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kas_transaksi';

    protected $fillable = [
        'tanggal',
        'type',
        'amount',
        'metode',
        'kategori',
        'keterangan',
        'pihak',
        'reference_type',
        'reference_id',
        'external_ref',
        'idempotency_key',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
}
