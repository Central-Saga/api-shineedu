<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsensiResource extends JsonResource
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
            'status_kehadiran' => $this->status_kehadiran,
            'jam_masuk' => $this->jam_masuk ? $this->jam_masuk->format('H:i') : null,
            'jam_pulang' => $this->jam_pulang ? $this->jam_pulang->format('H:i') : null,
            'durasi' => $this->durasi,
            'durasi_menit' => $this->durasi,
            'durasi_jam' => $this->durasi ? round($this->durasi / 60, 2) : 0,
            'durasi_formatted' => $this->durasi ? (floor($this->durasi / 60) . 'j ' . ($this->durasi % 60) . 'm') : '-',
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'sumber_absen' => $this->sumber_absen,
            'catatan' => $this->catatan,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'foto_masuk_url' => $this->getFirstMediaUrl('attendance_photos') ?: null,
            'foto_pulang_url' => $this->getFirstMediaUrl('attendance_photos_out') ?: null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'karyawan' => new EmployeeResource($this->whenLoaded('karyawan')),
        ];
    }
}
