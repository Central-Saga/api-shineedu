<?php

namespace App\Modules\Blog\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogAssetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'blog_id' => $this->blog_id,
            'file_path' => $this->file_path,
            'file_url' => $this->file_url,
            'title' => $this->title,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
