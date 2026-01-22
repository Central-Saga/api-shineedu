<?php

namespace App\Modules\Scheduling\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRealisasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'jadwal_kerja_id' => 'required|exists:jadwal_kerja,id',
            'status' => 'required|string|max:50', // Hadir, Izin, etc.
            'disetujui_oleh' => 'nullable|exists:users,id',
            'sumber' => 'nullable|string|in:MANUAL,SYSTEM',
            'catatan' => 'nullable|string',
            'ruangan_kelas' => 'nullable|string|max:255',
            'guru_pengajar_id' => 'nullable|exists:users,id',
            'guru_pengganti_id' => 'nullable|exists:users,id',
        ];
    }
}
