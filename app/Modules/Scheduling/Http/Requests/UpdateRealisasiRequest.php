<?php

namespace App\Modules\Scheduling\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRealisasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'sometimes|required|date',
            'jadwal_kerja_id' => 'sometimes|required|exists:jadwal_kerja,id',
            'status' => 'sometimes|required|string|max:50',
            'disetujui_oleh' => 'nullable|exists:users,id',
            'sumber' => 'nullable|string|in:MANUAL,SYSTEM',
            'catatan' => 'nullable|string',
            'ruangan_kelas' => 'nullable|string|max:255',
            'guru_pengajar_id' => 'nullable|exists:users,id',
            'guru_pengganti_id' => 'nullable|exists:users,id',
        ];
    }
}
