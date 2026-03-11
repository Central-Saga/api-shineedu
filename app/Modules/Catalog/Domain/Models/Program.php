<?php

namespace App\Modules\Catalog\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory, SoftDeletes, \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'program';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'image',
        'fitur',
        'status',
        'is_highlight',
    ];

    protected $casts = [
        'fitur' => 'array',
        'is_highlight' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }

    public function scopeHighlighted($query)
    {
        return $query->where('is_highlight', true);
    }

    public function jenjangs()
    {
        return $this->belongsToMany(Jenjang::class, 'program_jenjang', 'program_id', 'jenjang_id')
            ->withTimestamps();
    }
}
