<?php

namespace App\Modules\AcademicSessions\Domain\Models;

use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SesiAbsensiMurid extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sesi_absensi_murid';

    protected $fillable = [
        'realisasi_jadwal_kerja_id',
        'enrollment_id',
        'status', // HADIR, IZIN, SAKIT, ALPHA, BATAL
        'catatan',
        'created_by',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class, 'realisasi_jadwal_kerja_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
