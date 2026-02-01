<?php

namespace App\Modules\Catalog\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaketHarga extends Model
{
    use HasFactory, SoftDeletes, \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'paket_harga';

    protected $fillable = [
        'program_id',
        'jenjang_id',
        'paket_id',
        'min_siswa',
        'max_siswa',
        'harga',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected $casts = [
        'min_siswa' => 'integer',
        'max_siswa' => 'integer',
        'harga' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class);
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }
}
