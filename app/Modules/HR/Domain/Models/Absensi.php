<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Absensi extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'absensi';

    protected $fillable = [
        'karyawan_id',
        'status_kehadiran',
        'jam_masuk',
        'jam_pulang',
        'durasi',
        'tanggal',
        'sumber_absen',
        'catatan',
        'latitude',
        'longitude',
        'qr_code_data',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime', // Or string H:i? Cast to datetime handles parsing
        'jam_pulang' => 'datetime',
        'durasi' => 'integer',
        'karyawan_id' => 'integer',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'karyawan_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attendance_photos')
            ->useDisk('public')
            ->singleFile();
        $this->addMediaCollection('attendance_photos_out')
            ->useDisk('public')
            ->singleFile();
    }
}
