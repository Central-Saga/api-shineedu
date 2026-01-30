<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobVacancyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'location' => $this->location,
            'employment_type' => $this->employment_type,
            'description' => $this->description,
            'posted_at' => $this->posted_at?->format('Y-m-d'),
            'end_at' => $this->end_at?->format('Y-m-d'),
            'requirements' => $this->requirements ?? [],
            'responsibilities' => $this->responsibilities ?? [],
            'benefits' => $this->benefits ?? [],
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
