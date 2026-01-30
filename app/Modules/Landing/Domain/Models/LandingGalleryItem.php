<?php

namespace App\Modules\Landing\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LandingGalleryItem extends Model
{
    protected $table = 'landing_gallery_items';

    protected $fillable = [
        'title',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
