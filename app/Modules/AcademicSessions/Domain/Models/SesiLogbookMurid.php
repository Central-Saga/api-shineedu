<?php

namespace App\Modules\AcademicSessions\Domain\Models;

use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SesiLogbookMurid extends Model
{
    use HasFactory, SoftDeletes, \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'sesi_logbook_murid';

    protected $fillable = [
        'realisasi_jadwal_kerja_id',
        'enrollment_id',
        'catatan_perkembangan',
        'kesulitan',
        'target_next',
        'tugas_individu',
        'nilai_opsional',
        'created_by',
    ];

    protected $casts = [
        'nilai_opsional' => 'decimal:2',
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
