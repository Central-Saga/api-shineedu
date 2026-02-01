<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobVacancy extends Model
{
    use \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'job_vacancies';

    protected $fillable = [
        'title',
        'location',
        'employment_type',
        'description',
        'posted_at',
        'end_at',
        'requirements',
        'responsibilities',
        'benefits',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'posted_at' => 'date',
        'end_at' => 'date',
        'requirements' => 'array',
        'responsibilities' => 'array',
        'benefits' => 'array',
    ];

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_vacancy_id');
    }
}
