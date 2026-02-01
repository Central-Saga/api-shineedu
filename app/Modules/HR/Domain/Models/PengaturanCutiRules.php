<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanCutiRules extends Model
{
    use HasFactory, \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'pengaturan_cuti_rules';

    protected $fillable = [
        'kategori_karyawan',
        'subtipe_kontrak',
        'divisi',
        'jenis',
        'periode',
        'maksimal_pengajuan',
        'minimal_hari_pengajuan',
        'potongan_tipe',
        'potongan_nilai',
        'aktif',
    ];

    protected $casts = [
        'maksimal_pengajuan' => 'integer',
        'minimal_hari_pengajuan' => 'integer',
        'potongan_nilai' => 'decimal:2',
        'aktif' => 'boolean',
    ];
}
