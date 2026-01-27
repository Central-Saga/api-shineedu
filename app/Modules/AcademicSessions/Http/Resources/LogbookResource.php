<?php

namespace App\Modules\AcademicSessions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogbookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sesi_id' => $this->realisasi_jadwal_kerja_id,
            'ringkasan' => $this->ringkasan,
            'materi' => $this->materi,
            'homework' => $this->homework,
            'catatan_pengajar' => $this->catatan_pengajar,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'session' => new SessionResource($this->whenLoaded('session')),
        ];
    }
}
