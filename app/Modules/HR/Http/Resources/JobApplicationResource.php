<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $vacancy = $this->jobVacancy;

        return [
            'id' => $this->id,
            'position_id' => $this->job_vacancy_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'experience' => $this->experience,
            'education' => $this->education,
            'address' => $this->address,
            'resume_url' => $this->getFirstMediaUrl('resume'),
            'cover_letter_url' => $this->getFirstMediaUrl('cover_letter'),
            'status' => $this->status,
            'tracking_code' => $this->tracking_code,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'position' => $vacancy ? [
                'id' => $vacancy->id,
                'title' => $vacancy->title,
                'location' => $vacancy->location,
            ] : null,
        ];
    }
}
