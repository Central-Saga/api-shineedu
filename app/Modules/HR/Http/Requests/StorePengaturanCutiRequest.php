<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengaturanCutiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_karyawan' => 'required|string',
            'subtipe_kontrak' => 'nullable|string',
            'jenis' => 'required|string|in:cuti,izin,sakit',
            'periode' => 'required|string|in:bulanan,tahunan',
            'maksimal_pengajuan' => 'nullable|integer|min:0',
            'minimal_hari_pengajuan' => 'required|integer|min:0',
            'potongan_tipe' => 'required|string|in:per_hari,flat,none',
            'potongan_nilai' => 'nullable|numeric|min:0',
            'aktif' => 'boolean',
        ];
    }
}
