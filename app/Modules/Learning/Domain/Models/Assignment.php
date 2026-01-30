<?php

namespace App\Modules\Learning\Domain\Models;

use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Scheduling\Domain\Models\RealisasiJadwalKerja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assignment';

    protected $fillable = [
        'enrollment_id',
        'realisasi_jadwal_kerja_id',
        'materi_modul_id',
        'title',
        'instructions',
        'due_at',
        'status',
        'assigned_by',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Constants for status enum
    public const STATUS_ASSIGNED = 'ASSIGNED';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_REVIEWED = 'REVIEWED';
    public const STATUS_CLOSED = 'CLOSED';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ASSIGNED,
            self::STATUS_SUBMITTED,
            self::STATUS_REVIEWED,
            self::STATUS_CLOSED,
        ];
    }

    // Relationships

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function sesi()
    {
        return $this->belongsTo(RealisasiJadwalKerja::class, 'realisasi_jadwal_kerja_id');
    }

    public function materiModul()
    {
        return $this->belongsTo(MateriModul::class, 'materi_modul_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'assignment_id');
    }

    public function latestSubmission()
    {
        return $this->hasOne(AssignmentSubmission::class, 'assignment_id')->latestOfMany();
    }

    // Scopes

    public function scopeByEnrollment($query, $enrollmentId)
    {
        return $query->where('enrollment_id', $enrollmentId);
    }

    public function scopeBySesi($query, $sesiId)
    {
        return $query->where('realisasi_jadwal_kerja_id', $sesiId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereIn('status', [self::STATUS_ASSIGNED]);
    }

    // Helper methods

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast() && $this->status === self::STATUS_ASSIGNED;
    }

    public function canSubmit(): bool
    {
        return in_array($this->status, [self::STATUS_ASSIGNED, self::STATUS_SUBMITTED]);
    }

    public function close(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }
}
