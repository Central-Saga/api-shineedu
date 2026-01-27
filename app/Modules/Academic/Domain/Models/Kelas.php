<?php

namespace App\Modules\Academic\Domain\Models;

use App\Modules\Catalog\Domain\Models\Jenjang;
use App\Modules\Catalog\Domain\Models\Program;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kelas';

    protected $fillable = [
        'kode_kelas',
        'nama_kelas',
        'program_id',
        'jenjang_id',
        'tipe_kelas',
        'mode_private',
        'kapasitas',
        'status',
        'periode_mulai',
        'periode_selesai',
        'ruangan_default',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class, 'jenjang_id');
    }

    public function enrollments()
    {
        return $this->belongsToMany(Enrollment::class, 'kelas_enrollment', 'kelas_id', 'enrollment_id')
            ->withPivot('status_anggota', 'tanggal_masuk', 'tanggal_keluar')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedules()
    {
        return $this->hasMany(\App\Modules\Scheduling\Domain\Models\JadwalKerja::class, 'kelas_id');
    }
}
