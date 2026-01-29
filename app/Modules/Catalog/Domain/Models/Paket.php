<?php

namespace App\Modules\Catalog\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'paket';

    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'pertemuan_per_bulan',
        'durasi_menit',
        'boleh_mix_mapel',
        'max_mapel',
        'bisa_tambah_pertemuan',
        'bisa_ganti_hari',
        'status',
    ];

    protected $casts = [
        'pertemuan_per_bulan' => 'integer',
        'durasi_menit' => 'integer',
        'boleh_mix_mapel' => 'boolean',
        'max_mapel' => 'integer',
        'bisa_tambah_pertemuan' => 'boolean',
        'bisa_ganti_hari' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }

    public function hargas()
    {
        return $this->hasMany(PaketHarga::class, 'paket_id');
    }
}
