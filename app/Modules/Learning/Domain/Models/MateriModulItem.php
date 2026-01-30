<?php

namespace App\Modules\Learning\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriModulItem extends Model
{
    use HasFactory;

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
        'order_no' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constants for type enum
    public const TYPE_VIDEO = 'VIDEO';
    public const TYPE_PDF = 'PDF';
    public const TYPE_LINK = 'LINK';
    public const TYPE_TEXT = 'TEXT';
    public const TYPE_QUIZ = 'QUIZ';
    public const TYPE_FILE = 'FILE';

    public static function getTypes(): array
    {
        return [
            self::TYPE_VIDEO,
            self::TYPE_PDF,
            self::TYPE_LINK,
            self::TYPE_TEXT,
            self::TYPE_QUIZ,
            self::TYPE_FILE,
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
