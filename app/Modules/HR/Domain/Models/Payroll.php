<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payrolls';

    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'gaji_pokok',
        'total_fee_mengajar',
        'total_potongan',
        'gaji_bersih',
        'detail_potongan',
        'detail_pendapatan',
        'status',
        'tanggal_pembayaran',
        'catatan',
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'total_fee_mengajar' => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'gaji_bersih' => 'decimal:2',
        'detail_potongan' => 'array',
        'detail_pendapatan' => 'array',
        'tanggal_pembayaran' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'karyawan_id');
    }
}
