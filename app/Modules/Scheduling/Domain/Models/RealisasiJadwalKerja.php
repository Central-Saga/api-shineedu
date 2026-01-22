<?php

namespace App\Modules\Scheduling\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealisasiJadwalKerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'realisasi_jadwal_kerja';

    protected $fillable = [
        'tanggal',
        'jadwal_kerja_id',
        'status',
        'disetujui_oleh',
        'sumber',
        'catatan',
        'ruangan_kelas',
        'guru_pengajar_id',
        'guru_pengganti_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function jadwalKerja()
    {
        return $this->belongsTo(JadwalKerja::class, 'jadwal_kerja_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function guruPengajar()
    {
        return $this->belongsTo(User::class, 'guru_pengajar_id');
    }

    public function guruPengganti()
    {
        return $this->belongsTo(User::class, 'guru_pengganti_id');
    }
}
