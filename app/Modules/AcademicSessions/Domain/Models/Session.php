<?php

namespace App\Modules\AcademicSessions\Domain\Models;

use App\Modules\Academic\Domain\Models\Kelas;
use App\Modules\HR\Domain\Models\Employee;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'realisasi_jadwal_kerja';

    protected $fillable = [
        'tanggal',
        'jadwal_kerja_id',
        'kelas_id',
        'status',          // status umum (deprecated in favor of status_sesi, but kept for compatibility)
        'status_sesi',     // TERJADWAL, BERJALAN, SELESAI, BATAL, LIBUR
        'status_kehadiran_guru', // HADIR, IZIN, SAKIT, ALPHA, DIGANTI
        'disetujui_oleh',
        'sumber',          // MANUAL, SYSTEM
        'catatan',
        'ruangan_kelas',
        'guru_pengajar_id',
        'guru_pengganti_id',
        'jam_mulai_aktual',
        'jam_selesai_aktual',
        'dibatalkan_pada',
        'dibatalkan_oleh',
        'alasan_batal',
        'is_hangus',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai_aktual' => 'datetime',   // or string H:i
        'jam_selesai_aktual' => 'datetime',
        'dibatalkan_pada' => 'datetime',
        'is_hangus' => 'boolean',
    ];

    // Relationships
    public function jadwal()
    {
        return $this->belongsTo(JadwalKerja::class, 'jadwal_kerja_id');
    }

    public function jadwalKerja()
    {
        return $this->belongsTo(JadwalKerja::class, 'jadwal_kerja_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function guruPengajar()
    {
        return $this->belongsTo(Employee::class, 'guru_pengajar_id'); // Or User? Schema says users table, model link to likely Employee if they are same ID space or User.
        // Existing JadwalKerja maps guru_pengajar_id to Employee. Let's assume Employee.
        // Migration: constrained('users'). Employee usually extends or link to User.
        // Let's stick to User for FK user, but if the system uses Employee model for teachers, we use that.
        // JadwalKerja.php uses `belongsTo(Employee::class, 'guru_pengajar_id')`.
        // So I will use Employee::class.
    }

    public function guruPengganti()
    {
        return $this->belongsTo(Employee::class, 'guru_pengganti_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    public function absensi()
    {
        return $this->hasMany(SesiAbsensiMurid::class, 'realisasi_jadwal_kerja_id');
    }

    public function logbook()
    {
        return $this->hasOne(SesiLogbook::class, 'realisasi_jadwal_kerja_id');
    }

    public function logbookMurid()
    {
        return $this->hasMany(SesiLogbookMurid::class, 'realisasi_jadwal_kerja_id');
    }
}
