<?php

namespace App\Modules\Scheduling\Domain\Models;

use App\Modules\HR\Domain\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalKerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_kerja';

    protected $fillable = [
        'kategori',
        'mata_pelajaran',
        'hari',
        'nomor_sesi',
        'jam_mulai',
        'jam_selesai',
        'tarif',
        'status',
        'ruangan_kelas',
        'guru_pengajar_id',
    ];

    protected $casts = [
        'tarif' => 'decimal:2',
    ];

    public function guru()
    {
        return $this->belongsTo(Employee::class, 'guru_pengajar_id');
    }

    public function realisasi()
    {
        return $this->hasMany(RealisasiJadwalKerja::class, 'jadwal_kerja_id');
    }
}
