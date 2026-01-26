<?php

namespace App\Modules\AcademicSessions\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SesiLogbook extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sesi_logbook';

    protected $fillable = [
        'realisasi_jadwal_kerja_id',
        'ringkasan',
        'materi',
        'homework',
        'catatan_pengajar',
        'created_by',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class, 'realisasi_jadwal_kerja_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
