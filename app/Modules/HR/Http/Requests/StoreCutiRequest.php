<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCutiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'karyawan_id' => 'required|exists:karyawan,id',
            'jenis' => 'required|string|in:cuti,izin,sakit',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'keterangan' => 'nullable|string',
            'catatan' => 'nullable|string',
            'tanggal' => 'nullable|date', // Optional override
            'bukti_pendukung' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}
