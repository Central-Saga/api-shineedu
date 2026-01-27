<?php

namespace App\Modules\AcademicSessions\Http\Resources;

use App\Modules\Enrollment\Http\Resources\EnrollmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogbookMuridResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sesi_id' => $this->realisasi_jadwal_kerja_id,
            'enrollment_id' => $this->enrollment_id,
            'catatan_perkembangan' => $this->catatan_perkembangan,
            'kesulitan' => $this->kesulitan,
            'target_next' => $this->target_next,
            'tugas_individu' => $this->tugas_individu,
            'nilai_opsional' => $this->nilai_opsional,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'enrollment' => new EnrollmentResource($this->whenLoaded('enrollment')),
            'session' => new SessionResource($this->whenLoaded('session')),
        ];
    }
}
