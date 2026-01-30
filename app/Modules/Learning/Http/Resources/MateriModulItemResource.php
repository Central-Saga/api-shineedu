<?php

namespace App\Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MateriModulItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'materi_modul_id' => $this->materi_modul_id,
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'url' => $this->url,
            'file_path' => $this->file_path,
            'file_url' => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'order_no' => $this->order_no,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
