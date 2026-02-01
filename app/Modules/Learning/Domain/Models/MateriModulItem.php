<?php

namespace App\Modules\Learning\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriModulItem extends Model
{
    use HasFactory, \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'materi_modul_item';

    protected $fillable = [
        'materi_modul_id',
        'type',
        'title',
        'content',
        'url',
        'file_path',
        'order_no',
        'is_active',
    ];

    protected $casts = [
        'type' => 'string',
        'order_no' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constants for type enum
    public const TYPE_FILE = 'FILE';
    public const TYPE_URL = 'URL';

    public static function getTypes(): array
    {
        return [
            self::TYPE_FILE,
            self::TYPE_URL,
        ];
    }

    // Relationships

    public function materiModul()
    {
        return $this->belongsTo(MateriModul::class, 'materi_modul_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_no');
    }
}
