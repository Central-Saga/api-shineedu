<?php

namespace App\Modules\Student\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MuridResource extends JsonResource
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
            'kode_murid' => $this->kode_murid,
            'nama_lengkap' => $this->nama_lengkap,
            'jenis_kelamin' => $this->jenis_kelamin,
            'tanggal_lahir' => $this->tanggal_lahir ? $this->tanggal_lahir->format('Y-m-d') : null,
            'no_hp' => $this->no_hp,
            'no_hp_display' => $this->no_hp ?: $this->no_hp_wali, // Fallback to wali if empty
            'email' => $this->email,
            'alamat' => $this->alamat,

            'jenjang' => $this->whenLoaded('jenjang', function () {
                return [
                    'id' => $this->jenjang->id,
                    'nama' => $this->jenjang->nama,
                    'kode' => $this->jenjang->kode,
                ];
            }),
            'jenjang_id' => $this->jenjang_id,

            'sekolah_asal' => $this->sekolah_asal,
            'kelas_sekolah' => $this->kelas_sekolah,

            'nama_wali' => $this->nama_wali,
            'no_hp_wali' => $this->no_hp_wali,
            'email_wali' => $this->email_wali,
            'hubungan_wali' => $this->hubungan_wali,

            'catatan_khusus' => $this->catatan_khusus,
            'kebutuhan_khusus' => $this->kebutuhan_khusus,

            'status' => $this->status,
            'enrollments' => \App\Modules\Enrollment\Http\Resources\EnrollmentResource::collection($this->whenLoaded('enrollments')),
            'absensi_summary' => $this->whenLoaded('absensi', function () {
                return [
                    'total' => $this->absensi->count(),
                    'hadir' => $this->absensi->where('status', 'HADIR')->count(),
                    'tidak_hadir' => $this->absensi->where('status', 'TIDAK_HADIR')->count(),
                ];
            }),
            'absensi_history' => $this->whenLoaded('absensi', function () {
                return $this->absensi->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'status' => $a->status,
                        'catatan' => $a->catatan,
                        'tanggal' => $a->session ? $a->session->tanggal->format('Y-m-d') : null,
                        'jam' => $a->session ? $a->session->jam_mulai_plan : null,
                    ];
                })->sortByDesc('tanggal')->values();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
