<?php

namespace App\Modules\Catalog\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'nama' => $this->nama,
            'tipe' => $this->tipe,
            'pertemuan_per_bulan' => $this->pertemuan_per_bulan,
            'durasi_menit' => $this->durasi_menit,
            'boleh_mix_mapel' => $this->boleh_mix_mapel,
            'max_mapel' => $this->max_mapel,
            'bisa_tambah_pertemuan' => $this->bisa_tambah_pertemuan,
            'bisa_ganti_hari' => $this->bisa_ganti_hari,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
