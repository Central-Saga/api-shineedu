<?php

namespace App\Modules\Catalog\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProgramResource extends JsonResource
{
    /**
     * Resolve image to full URL for landing/catalog.
     * If image is storage path (no scheme), return public URL; otherwise return as-is (e.g. external URL).
     */
    protected function imageUrl(): ?string
    {
        $image = $this->image;
        if (empty($image)) {
            return null;
        }
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }
        $url = Storage::disk('public')->url($image);
        if (str_starts_with($url, '/')) {
            $url = rtrim(config('app.url', ''), '/') . $url;
        }
        return $url;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'image' => $this->imageUrl(),
            'fitur' => $this->fitur ?? [],
            'status' => $this->status,
            'is_highlight' => (bool) $this->is_highlight,
            'jenjangs' => JenjangResource::collection($this->whenLoaded('jenjangs')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
