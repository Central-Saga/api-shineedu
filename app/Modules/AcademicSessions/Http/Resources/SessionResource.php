<?php

namespace App\Modules\AcademicSessions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kelas_id' => $this->kelas_id,
            'kelas_nama' => $this->whenLoaded('kelas', fn() => $this->kelas->nama),
            'jadwal_kerja_id' => $this->jadwal_kerja_id,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'hari_indo' => $this->tanggal->isoFormat('dddd'),
            'jam_mulai_plan' => $this->jadwal ? $this->jadwal->jam_mulai : null,
            'jam_selesai_plan' => $this->jadwal ? $this->jadwal->jam_selesai : null,
            'jam_mulai_aktual' => $this->jam_mulai_aktual ? $this->jam_mulai_aktual->format('H:i') : null,
            'jam_selesai_aktual' => $this->jam_selesai_aktual ? $this->jam_selesai_aktual->format('H:i') : null,
            'status_sesi' => $this->status_sesi,
            'status_kehadiran_guru' => $this->status_kehadiran_guru,
            'guru_pengajar' => new \App\Modules\HR\Http\Resources\EmployeeResource($this->whenLoaded('guruPengajar')), // Assuming EmployeeResource exists
            'guru_pengganti' => new \App\Modules\HR\Http\Resources\EmployeeResource($this->whenLoaded('guruPengganti')),
            'ruangan_kelas' => $this->ruangan_kelas,
            'catatan' => $this->catatan,
            'dibatalkan_pada' => $this->dibatalkan_pada,
            'alasan_batal' => $this->alasan_batal,
            'logbook' => $this->whenLoaded('logbook'),
            'absensi_count' => $this->whenLoaded('absensi', fn() => $this->absensi->count()),
        ];
    }
}
