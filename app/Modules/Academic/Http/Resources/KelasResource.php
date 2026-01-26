<?php

namespace App\Modules\Academic\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KelasResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kode_kelas' => $this->kode_kelas,
            'nama_kelas' => $this->nama_kelas,
            'program_id' => $this->program_id,
            'jenjang_id' => $this->jenjang_id,
            'program' => $this->whenLoaded('program'),
            'jenjang' => $this->whenLoaded('jenjang'),
            'tipe_kelas' => $this->tipe_kelas,
            'mode_private' => $this->mode_private,
            'kapasitas' => $this->kapasitas,
            'status' => $this->status,
            'periode_mulai' => $this->periode_mulai,
            'periode_selesai' => $this->periode_selesai,
            'ruangan_default' => $this->ruangan_default,
            'catatan' => $this->catatan,
            'created_by' => $this->whenLoaded('creator'),
            'enrollments_count' => $this->when(isset($this->enrollments_count), $this->enrollments_count),
            'enrollments' => $this->whenLoaded('enrollments'), // Will include pivot and enrollment details
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
