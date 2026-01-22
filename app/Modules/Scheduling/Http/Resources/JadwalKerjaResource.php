<?php

namespace App\Modules\Scheduling\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JadwalKerjaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kategori' => $this->kategori,
            'mata_pelajaran' => $this->mata_pelajaran,
            'hari' => $this->hari,
            'nomor_sesi' => $this->nomor_sesi,
            'jam_mulai' => $this->jam_mulai ? \Carbon\Carbon::parse($this->jam_mulai)->format('H:i') : null,
            'jam_selesai' => $this->jam_selesai ? \Carbon\Carbon::parse($this->jam_selesai)->format('H:i') : null,
            'tarif' => (float) $this->tarif,
            'status' => $this->status,
            'ruangan_kelas' => $this->ruangan_kelas,
            'guru_pengajar_id' => $this->guru_pengajar_id,
            'guru_pengajar' => $this->whenLoaded('guru', function () {
                return [
                    'id' => $this->guru->id,
                    'kode_karyawan' => $this->guru->kode_karyawan,
                    'kategori_karyawan' => $this->guru->kategori_karyawan,
                    'user' => [
                        'id' => $this->guru->user->id,
                        'name' => $this->guru->user->name,
                        'email' => $this->guru->user->email,
                    ],
                    'kontak' => [
                        'nomor_hp' => $this->guru->nomor_hp,
                    ],
                ];
            }),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
