<?php

namespace App\Modules\Learning\Domain\Models;

use App\Modules\Academic\Domain\Models\KelasEnrollment;
use App\Modules\Academic\Domain\Models\RealisasiJadwalKerja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesiMuridAssignment extends Model
{
    protected $table = 'sesi_murid_assignment';

    public $timestamps = false;

    protected $fillable = [
        'realisasi_jadwal_kerja_id',
        'enrollment_id',
        'assignment_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
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

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }
}
