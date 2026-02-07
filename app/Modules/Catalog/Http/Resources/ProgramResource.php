<?php

namespace App\Modules\Catalog\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'image' => $this->image,
            'fitur' => $this->fitur ?? [],
            'status' => $this->status,
            'is_highlight' => (bool) $this->is_highlight,
            'jenjangs' => JenjangResource::collection($this->whenLoaded('jenjangs')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
