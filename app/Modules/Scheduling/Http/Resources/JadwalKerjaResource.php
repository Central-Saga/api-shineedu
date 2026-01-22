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
            'jam_mulai' => $this->jam_mulai, // usually formatted by default or accessor? keeping raw for now
            'jam_selesai' => $this->jam_selesai,
            'tarif' => (float) $this->tarif,
            'status' => $this->status,
            'ruangan_kelas' => $this->ruangan_kelas,
            'guru_pengajar_id' => $this->guru_pengajar_id,
            'guru_pengajar' => $this->whenLoaded('guru', function () {
                return [
                    'id' => $this->guru->id,
                    'name' => $this->guru->name,
                    'email' => $this->guru->email,
                ];
            }),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
