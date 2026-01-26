<?php

namespace App\Modules\Enrollment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_enrollment' => $this->kode_enrollment,
            'murid_id' => $this->murid_id,
            'murid' => $this->whenLoaded('murid', function () {
                return [
                    'id' => $this->murid->id,
                    'nama_lengkap' => $this->murid->nama_lengkap,
                    'kode_murid' => $this->murid->kode_murid,
                    'no_hp' => $this->murid->no_hp,
                ];
            }),
            'program' => $this->whenLoaded('program', function () {
                return [
                    'id' => $this->program->id,
                    'nama' => $this->program->nama, // Assuming 'nama' or 'name'
                ];
            }),
            'jenjang' => $this->whenLoaded('jenjang', function () {
                return [
                    'id' => $this->jenjang->id,
                    'nama' => $this->jenjang->nama,
                ];
            }),
            'paket' => $this->whenLoaded('paket', function () {
                return [
                    'id' => $this->paket->id,
                    'nama' => $this->paket->nama,
                ];
            }),
            'jumlah_siswa' => $this->jumlah_siswa,
            'harga_final' => (float) $this->harga_final,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'status' => $this->status,
            'catatan' => $this->catatan,
            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'biaya_pendaftaran_amount' => (float) $this->biaya_pendaftaran_amount,
            'biaya_pendaftaran_status' => $this->biaya_pendaftaran_status,
            'biaya_pendaftaran_due_date' => $this->biaya_pendaftaran_due_date,
            'registration_fee_transaction_id' => $this->registration_fee_transaction_id,
            'kelas' => \App\Modules\Academic\Http\Resources\KelasResource::collection($this->whenLoaded('kelas')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
