<?php

namespace App\Modules\Landing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'featured_image_path' => $this->featured_image_path,
            'featured_image_url' => $this->featured_image_url,
            'status' => $this->status,
            'category' => $this->category,
            'author_id' => $this->author_id,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'email' => $this->author->email,
            ]),
            'assets' => BlogAssetResource::collection($this->whenLoaded('assets')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
