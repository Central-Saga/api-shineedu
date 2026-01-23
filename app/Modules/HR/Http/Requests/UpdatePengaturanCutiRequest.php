<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengaturanCutiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_karyawan' => 'sometimes|string',
            'subtipe_kontrak' => 'nullable|string',
            'jenis' => 'sometimes|string|in:cuti,izin,sakit',
            'periode' => 'sometimes|string|in:bulanan,tahunan',
            'maksimal_pengajuan' => 'nullable|integer|min:0',
            'minimal_hari_pengajuan' => 'sometimes|integer|min:0',
            'potongan_tipe' => 'sometimes|string|in:per_hari,flat,none',
            'potongan_nilai' => 'nullable|numeric|min:0',
            'aktif' => 'boolean',
        ];
    }
}
