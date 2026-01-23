<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengaturanCutiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kategori_karyawan' => $this->kategori_karyawan,
            'subtipe_kontrak' => $this->subtipe_kontrak,
            'kategori_mapel' => $this->kategori_mapel,
            'jenis' => $this->jenis,
            'periode' => $this->periode,
            'maksimal_pengajuan' => $this->maksimal_pengajuan,
            'minimal_hari_pengajuan' => $this->minimal_hari_pengajuan,
            'potongan_tipe' => $this->potongan_tipe,
            'potongan_nilai' => $this->potongan_nilai,
            'aktif' => (bool)$this->aktif,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
