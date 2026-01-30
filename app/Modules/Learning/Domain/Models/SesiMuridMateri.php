<?php

namespace App\Modules\Learning\Domain\Models;

use App\Modules\Academic\Domain\Models\KelasEnrollment;
use App\Modules\Academic\Domain\Models\RealisasiJadwalKerja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesiMuridMateri extends Model
{
    protected $table = 'sesi_murid_materi';

    public $timestamps = false;

    protected $fillable = [
        'realisasi_jadwal_kerja_id',
        'enrollment_id',
        'materi_modul_id',
        'assigned_at',
        'accessed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'accessed_at' => 'datetime',
    ];

    // Relationships

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(RealisasiJadwalKerja::class, 'realisasi_jadwal_kerja_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(KelasEnrollment::class, 'enrollment_id');
    }

    public function materi(): BelongsTo
    {
        return $this->belongsTo(MateriModul::class, 'materi_modul_id');
    }
}
