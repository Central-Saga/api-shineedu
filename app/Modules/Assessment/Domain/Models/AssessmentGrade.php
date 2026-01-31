<?php

namespace App\Modules\Assessment\Domain\Models;

use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\HR\Domain\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AssessmentGrade extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'assessment_grades';

    protected $fillable = [
        'enrollment_id',
        'certificate_template_id',
        'teacher_karyawan_id',
        'scores',
        'total_score',
        'average_score',
        'predicate',
        'certificate_level',
        'certificate_no',
        'generated_at',
        'payload_snapshot',
    ];

    protected $casts = [
        'scores' => 'array',
        'payload_snapshot' => 'array',
        'total_score' => 'decimal:2',
        'average_score' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function certificateTemplate(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'teacher_karyawan_id');
    }

    public function registerMediaCollections(): void
    {
        // One PDF per record
        $this->addMediaCollection('certificate_pdf')
            ->singleFile();
    }
}
