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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
