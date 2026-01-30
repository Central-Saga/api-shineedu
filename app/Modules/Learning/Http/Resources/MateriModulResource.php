<?php

namespace App\Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MateriModulResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'program_id' => $this->program_id,
            'program' => $this->whenLoaded('program', fn() => [
                'id' => $this->program->id,
                'nama' => $this->program->nama,
            ]),
            'jenjang_id' => $this->jenjang_id,
            'jenjang' => $this->whenLoaded('jenjang', fn() => [
                'id' => $this->jenjang->id,
                'nama' => $this->jenjang->nama,
            ]),
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'items' => MateriModulItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
