<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CutiResource extends JsonResource
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
            'karyawan_id' => $this->karyawan_id,
            'karyawan_nama' => $this->karyawan->user->name ?? $this->karyawan->kode_karyawan ?? null,
            'jenis' => $this->jenis,
            'status' => $this->status,
            'tanggal' => $this->tanggal,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'keterangan' => $this->keterangan,
            'disetujui_oleh' => $this->disetujui_oleh,
            'approver_name' => $this->approver->name ?? null,
            'potongan_tipe' => $this->potongan_tipe,
            'potongan_nilai' => $this->potongan_nilai,
            'bukti_pendukung_url' => $this->getFirstMediaUrl('bukti_cuti'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Include employee details for context if needed in list
            'karyawan' => new EmployeeResource($this->whenLoaded('karyawan')),
        ];
    }
}
