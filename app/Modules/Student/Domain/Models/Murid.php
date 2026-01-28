<?php

namespace App\Modules\Student\Domain\Models;

use App\Modules\Catalog\Domain\Models\Jenjang;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Murid extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'murid';

    protected $fillable = [
        'user_id',
        'kode_murid',
        'nama_lengkap',
        'jenis_kelamin',
        'tanggal_lahir',
        'no_hp',
        'email',
        'alamat',
        'jenjang_id',
        'sekolah_asal',
        'kelas_sekolah',
        'nama_wali',
        'no_hp_wali',
        'email_wali',
        'hubungan_wali',
        'catatan_khusus',
        'kebutuhan_khusus',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(Jenjang::class, 'jenjang_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
