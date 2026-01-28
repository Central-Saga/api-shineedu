<?php

namespace App\Modules\Enrollment\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketMuridLedger extends Model
{
    use HasFactory;

    protected $table = 'paket_murid_ledger';

    // Disable updated_at since ledger is immutable
    const UPDATED_AT = null;

    protected $fillable = [
        'paket_murid_id',
        'tanggal',
        'type',
        'qty',
        'reference_type',
        'reference_id',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'qty' => 'integer',
        'created_at' => 'datetime',
    ];

    // Ledger type constants
    const TYPE_TOPUP = 'TOPUP';
    const TYPE_USE = 'USE';
    const TYPE_ADJUST = 'ADJUST';
    const TYPE_EXPIRE = 'EXPIRE';
    const TYPE_REFUND = 'REFUND';

    // Reference type constants
    const REF_ATTENDANCE = 'attendance';
    const REF_ADMIN_ADJUST = 'admin_adjust';
    const REF_PURCHASE = 'purchase';
    const REF_ATTENDANCE_ROLLBACK = 'attendance_rollback';

    // Relationships

    public function paketMurid()
    {
        return $this->belongsTo(PaketMurid::class, 'paket_murid_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference model (polymorphic-like)
     * Note: This is a manual implementation since we're using type+id instead of morphTo
     */
    public function getReferenceAttribute()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        // Map reference types to models
        $modelMap = [
            self::REF_ATTENDANCE => \App\Modules\AcademicSessions\Domain\Models\SesiAbsensiMurid::class,
        ];

        $modelClass = $modelMap[$this->reference_type] ?? null;

        if ($modelClass) {
            return $modelClass::find($this->reference_id);
        }

        return null;
    }
}
