<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class JobApplication extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'job_applications';

    protected $fillable = [
        'job_vacancy_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'experience',
        'education',
        'address',
        'status',
        'tracking_code',
    ];

    protected $casts = [
        //
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('resume')->singleFile();
        $this->addMediaCollection('cover_letter')->singleFile();
    }

    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }
}
