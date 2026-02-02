<?php

namespace App\Modules\Landing\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class LandingGalleryItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $imageUrl = null;
        if ($this->image_path) {
            $imageUrl = Storage::disk('public')->url($this->image_path);
            // Pastikan URL absolut agar gambar bisa dimuat dari halaman landing (domain lain)
            if (str_starts_with($imageUrl, '/')) {
                $imageUrl = rtrim(config('app.url', ''), '/') . $imageUrl;
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'alt' => $this->title,
            'image_path' => $this->image_path,
            'image_url' => $imageUrl ?? '',
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
