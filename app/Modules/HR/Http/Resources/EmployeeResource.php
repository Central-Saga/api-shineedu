<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'kode_karyawan' => $this->kode_karyawan,
            'divisi' => $this->divisi,
            'status' => $this->status,
            'kategori_karyawan' => $this->kategori_karyawan,
            'subtipe_kontrak' => $this->subtipe_kontrak,
            'tipe_gaji' => $this->tipe_gaji,
            'gaji_pokok' => $this->gaji_pokok,
            'bank' => [
                'nama' => $this->bank_nama,
                'rekening' => $this->bank_no_rekening,
            ],
            'kontak' => [
                'nomor_hp' => $this->nomor_hp,
                'alamat' => $this->alamat,
            ],
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
