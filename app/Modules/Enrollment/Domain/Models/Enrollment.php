<?php

namespace App\Modules\Enrollment\Domain\Models;

use App\Modules\Catalog\Domain\Models\Jenjang;
use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Catalog\Domain\Models\Program;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Student\Domain\Models\Murid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes, \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'enrollments';

    protected $fillable = [
        'kode_enrollment',
        'murid_id',
        'program_id',
        'jenjang_id',
        'paket_id',
        'jumlah_siswa',
        'harga_final',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
        'created_by',
        'biaya_pendaftaran_amount',
        'biaya_pendaftaran_status',
        'biaya_pendaftaran_due_date',
        'registration_fee_transaction_id',
        'sumber',
        'external_reference_id',
        'saldo_override',
    ];

    protected $casts = [
        'harga_final' => 'decimal:2',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_siswa' => 'integer',
        'biaya_pendaftaran_amount' => 'decimal:2',
        'biaya_pendaftaran_due_date' => 'date',
        'saldo_override' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    /**
     * Alias for murid relationship to prevent "undefined relationship student" error.
     */
    public function student()
    {
        return $this->murid();
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class, 'jenjang_id');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kelas()
    {
        return $this->belongsToMany(\App\Modules\Academic\Domain\Models\Kelas::class, 'kelas_enrollment', 'enrollment_id', 'kelas_id')
            ->withPivot('status_anggota', 'tanggal_masuk', 'tanggal_keluar')
            ->withTimestamps();
    }

    public function absensi()
    {
        return $this->hasMany(\App\Modules\AcademicSessions\Domain\Models\SesiAbsensiMurid::class, 'enrollment_id');
    }

    public function paketMurid()
    {
        return $this->hasMany(PaketMurid::class, 'enrollment_id');
    }

    // Scopes

    /**
     * Scope untuk enrollment dari sistem internal
     */
    public function scopeInternal($query)
    {
        return $query->where('sumber', 'INTERNAL');
    }

    /**
     * Scope untuk enrollment dari i-seller/external
     */
    public function scopeExternal($query)
    {
        return $query->whereIn('sumber', ['ISELLER', 'IMPORT']);
    }

    // Helper Methods

    /**
     * Check apakah enrollment dari external system
     */
    public function isFromExternal(): bool
    {
        return in_array($this->sumber, ['ISELLER', 'IMPORT']);
    }

    /**
     * Get saldo override value
     * NULL = pakai sistem normal (ledger)
     * -1 = unlimited (tidak dicek)
     * >= 0 = saldo manual yang di-set admin
     */
    public function getSaldoOverride(): ?int
    {
        return $this->saldo_override;
    }

    /**
     * Check apakah saldo unlimited (tidak dicek)
     */
    public function isSaldoUnlimited(): bool
    {
        return $this->saldo_override === -1;
    }

    /**
     * Check apakah pakai saldo manual
     */
    public function isSaldoManual(): bool
    {
        return $this->saldo_override !== null && $this->saldo_override >= 0;
    }

    /**
     * Get current saldo dari ledger (kalau tidak ada override)
     */
    public function getCurrentSaldoFromLedger(): int
    {
        return $this->paketMurid->sum(function ($pm) {
            return $pm->saldo_current;
        });
    }
}
