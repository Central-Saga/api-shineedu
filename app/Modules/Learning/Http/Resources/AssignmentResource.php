<?php

namespace App\Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enrollment_id' => $this->enrollment_id,
            'enrollment' => $this->whenLoaded('enrollment', fn() => [
                'id' => $this->enrollment->id,
                'kode_enrollment' => $this->enrollment->kode_enrollment,
                'murid' => $this->enrollment->murid ? [
                    'id' => $this->enrollment->murid->id,
                    'nama_lengkap' => $this->enrollment->murid->nama_lengkap,
                ] : null,
            ]),
            'realisasi_jadwal_kerja_id' => $this->realisasi_jadwal_kerja_id,
            'sesi' => $this->whenLoaded('sesi', fn() => [
                'id' => $this->sesi->id,
                'tanggal' => $this->sesi->tanggal,
                'jam_mulai' => $this->sesi->jam_mulai,
                'jam_selesai' => $this->sesi->jam_selesai,
            ]),
            'materi_modul_id' => $this->materi_modul_id,
            'materi_modul' => $this->whenLoaded('materiModul', fn() => [
                'id' => $this->materiModul->id,
                'title' => $this->materiModul->title,
            ]),
            'title' => $this->title,
            'instructions' => $this->instructions,
            'attachment_type' => $this->attachment_type,
            'attachment_url' => $this->getAttachmentUrl(),
            'due_at' => $this->due_at?->toIso8601String(),
            'is_overdue' => $this->isOverdue(),
            'status' => $this->status,
            'assigned_by' => $this->assigned_by,
            'assigned_by_user' => $this->whenLoaded('assignedBy', fn() => [
                'id' => $this->assignedBy->id,
                'name' => $this->assignedBy->name,
            ]),
            'submissions_count' => $this->whenCounted('submissions'),
            'latest_submission' => $this->whenLoaded(
                'latestSubmission',
                fn() =>
                new AssignmentSubmissionResource($this->latestSubmission)
            ),
            'submissions' => AssignmentSubmissionResource::collection($this->whenLoaded('submissions')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Get attachment URL based on type.
     * For FILE type: return Spatie Media URL
     * For URL type: return direct URL
     */
    private function getAttachmentUrl(): ?string
    {
        if ($this->attachment_type === 'FILE') {
            $media = $this->getFirstMedia('attachments');
            return $media ? $media->getUrl() : null;
        }

        if ($this->attachment_type === 'URL') {
            return $this->attachment_url;
        }

        return null;
    }
}
