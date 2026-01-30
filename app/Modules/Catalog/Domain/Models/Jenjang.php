<?php

namespace App\Modules\Catalog\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jenjang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jenjang';

    protected $fillable = [
        'kode',
        'nama',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'program_jenjang', 'jenjang_id', 'program_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }
}
