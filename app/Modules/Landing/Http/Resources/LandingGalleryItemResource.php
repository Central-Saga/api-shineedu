<?php

namespace App\Modules\Landing\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LandingGalleryItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'alt' => $this->title,
            'image_path' => $this->image_path,
            'image_url' => $this->image_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
