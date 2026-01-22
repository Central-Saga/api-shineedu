<?php

namespace App\Modules\Scheduling\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJadwalKerjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori' => 'required|string|max:255',
            'mata_pelajaran' => 'nullable|string|max:255',
            'hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'nomor_sesi' => 'nullable|string|max:50',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tarif' => 'required|numeric|min:0',
            'status' => 'required|string|in:Aktif,Non Aktif',
            'ruangan_kelas' => 'nullable|string|max:255',
            'guru_pengajar_id' => 'required|exists:users,id',
        ];
    }
}
