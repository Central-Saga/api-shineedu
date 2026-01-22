<?php

namespace App\Modules\Scheduling\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJadwalKerjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori' => 'sometimes|required|string|max:255',
            'mata_pelajaran' => 'nullable|string|max:255',
            'hari' => 'sometimes|required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'nomor_sesi' => 'nullable|string|max:50',
            'jam_mulai' => 'sometimes|required|date_format:H:i',
            'jam_selesai' => 'sometimes|required|date_format:H:i|after:jam_mulai',
            'tarif' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|string|in:Aktif,Non Aktif',
            'ruangan_kelas' => 'nullable|string|max:255',
            'guru_pengajar_id' => 'sometimes|required|exists:karyawan,id',
        ];
    }
}
