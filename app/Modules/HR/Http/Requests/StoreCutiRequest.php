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
            'catatan' => 'nullable|string', // Alias for keterangan
            'tanggal' => 'nullable|date',
            'status' => 'nullable|string|in:diajukan,disetujui,ditolak,dibatalkan',
            'bukti_pendukung' => 'nullable|file|max:5120', // Max 5MB
        ];
    }
}
