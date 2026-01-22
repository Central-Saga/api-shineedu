<?php

namespace App\Modules\Scheduling\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RealisasiJadwalKerjaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal ? $this->tanggal->format('Y-m-d') : null,
            'jadwal_kerja_id' => $this->jadwal_kerja_id,
            'jadwal_kerja' => new JadwalKerjaResource($this->whenLoaded('jadwalKerja')),
            'status' => $this->status,
            'disetujui_oleh' => $this->disetujui_oleh,
            'approver' => $this->whenLoaded('approver', fn() => [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ]),
            'sumber' => $this->sumber,
            'catatan' => $this->catatan,
            'ruangan_kelas' => $this->ruangan_kelas,
            'guru_pengajar_id' => $this->guru_pengajar_id,
            'guru_pengajar' => $this->whenLoaded('guruPengajar', fn() => [
                'id' => $this->guruPengajar->id,
                'name' => $this->guruPengajar->name,
            ]),
            'guru_pengganti_id' => $this->guru_pengganti_id,
            'guru_pengganti' => $this->whenLoaded('guruPengganti', fn() => [
                'id' => $this->guruPengganti->id,
                'name' => $this->guruPengganti->name,
            ]),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
