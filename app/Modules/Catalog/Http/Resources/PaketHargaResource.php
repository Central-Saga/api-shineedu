<?php

namespace App\Modules\Catalog\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaketHargaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'program' => new ProgramResource($this->whenLoaded('program')),
            'jenjang' => new JenjangResource($this->whenLoaded('jenjang')),
            'paket' => new PaketResource($this->whenLoaded('paket')),
            'program_id' => $this->program_id,
            'jenjang_id' => $this->jenjang_id,
            'paket_id' => $this->paket_id,
            'min_siswa' => $this->min_siswa,
            'max_siswa' => $this->max_siswa,
            'harga' => $this->harga,
            'effective_from' => $this->effective_from,
            'effective_to' => $this->effective_to,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
