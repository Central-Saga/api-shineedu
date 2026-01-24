<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCutiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:diajukan,disetujui,ditolak,dibatalkan,pembatalan_diajukan',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'keterangan' => 'nullable|string',
            'catatan' => 'nullable|string',
            'disetujui_oleh' => 'nullable|exists:users,id',
            // Allow updating rule snapshot if needed (admin override)
            'potongan_tipe' => 'nullable|string',
            'potongan_nilai' => 'nullable|numeric',
            'bukti_pendukung' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}
