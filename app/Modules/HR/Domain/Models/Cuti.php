<?php

namespace App\Modules\HR\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Cuti extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'cuti';

    protected $fillable = [
        'karyawan_id',
        'jenis',
        'status',
        'tanggal',
        'start_date',
        'end_date',
        'keterangan',
        'disetujui_oleh',
        'potongan_tipe',
        'potongan_nilai',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'potongan_nilai' => 'decimal:2',
    ];

    // Relations
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'karyawan_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
