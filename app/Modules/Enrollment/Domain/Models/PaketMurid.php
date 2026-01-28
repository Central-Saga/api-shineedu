<?php

namespace App\Modules\Enrollment\Domain\Models;

use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaketMurid extends Model
{
    use HasFactory;

    protected $table = 'paket_murid';

    protected $fillable = [
        'enrollment_id',
        'paket_id',
        'status',
        'tanggal_mulai',
        'tanggal_berakhir',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function ledger()
    {
        return $this->hasMany(PaketMuridLedger::class, 'paket_murid_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'AKTIF');
    }

    public function scopeWithPositiveBalance($query)
    {
        return $query->whereHas('ledger')
            ->select('paket_murid.*')
            ->selectRaw('(SELECT COALESCE(SUM(qty), 0) FROM paket_murid_ledger WHERE paket_murid_id = paket_murid.id) as saldo_current')
            ->havingRaw('saldo_current > 0');
    }

    // Accessors

    /**
     * Calculate current balance from ledger entries
     */
    public function getSaldoCurrentAttribute()
    {
        return $this->ledger()->sum('qty');
    }

    /**
     * Get total top-up credits
     */
    public function getTotalTopupAttribute()
    {
        return $this->ledger()
            ->where('type', 'TOPUP')
            ->sum('qty');
    }

    /**
     * Get total used credits
     */
    public function getTotalUseAttribute()
    {
        return abs($this->ledger()
            ->where('type', 'USE')
            ->sum('qty'));
    }
}
