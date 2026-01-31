<?php

namespace App\Modules\Landing\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class BlogPost extends Model
{
    protected $table = 'landing_blog_posts';

    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'featured_image_path',
        'status',
        'category',
        'author_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (empty($this->featured_image_path)) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($this->featured_image_path);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(BlogAsset::class, 'blog_post_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** @param  Builder<BlogPost>  $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /** @param  Builder<BlogPost>  $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }
}
