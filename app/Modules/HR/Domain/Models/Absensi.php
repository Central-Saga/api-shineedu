<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Absensi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'absensi';

    protected $fillable = [
        'karyawan_id',
        'status_kehadiran',
        'jam_masuk',
        'jam_pulang',
        'durasi',
        'tanggal',
        'sumber_absen',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime', // Or string H:i? Cast to datetime handles parsing
        'jam_pulang' => 'datetime',
        'durasi' => 'integer',
        'karyawan_id' => 'integer',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'karyawan_id');
    }
}
