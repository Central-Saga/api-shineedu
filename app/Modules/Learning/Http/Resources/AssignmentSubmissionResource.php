<?php

namespace App\Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'submitted_by' => $this->submitted_by,
            'submitted_by_user' => $this->whenLoaded('submittedBy', fn() => [
                'id' => $this->submittedBy->id,
                'name' => $this->submittedBy->name,
            ]),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'content_text' => $this->content_text,
            'attachment_path' => $this->attachment_path,
            'attachment_url' => $this->attachment_path ? asset('storage/' . $this->attachment_path) : null,
            'status' => $this->status,
            'reviewed_by' => $this->reviewed_by,
            'reviewer' => $this->whenLoaded('reviewer', fn() => [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ]),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'feedback' => $this->feedback,
            'score' => $this->score,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
