<?php

namespace App\Modules\Assessment\Domain\Models;

use App\Modules\Assessment\Domain\Enums\CertificateType;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CertificateTemplate extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'assessment_certificate_templates';

    protected $fillable = [
        'name',
        'type',
        'data_mapping',
        'is_active',
    ];

    protected $casts = [
        'type' => CertificateType::class,
        'data_mapping' => 'array',
        'is_active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover_image')
            ->singleFile(); // The Canva background for Page 1

        $this->addMediaCollection('result_image')
            ->singleFile(); // The Canva background for Page 2 (Result details)
    }
}
