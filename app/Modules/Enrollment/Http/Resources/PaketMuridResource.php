<?php

namespace App\Modules\Enrollment\Http\Resources;

use App\Modules\Catalog\Http\Resources\PaketResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PaketMuridResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'enrollment_id' => $this->enrollment_id,
            'paket' => new PaketResource($this->whenLoaded('paket')),
            'status' => $this->status,
            'tanggal_mulai' => $this->tanggal_mulai ? $this->tanggal_mulai->format('Y-m-d') : null,
            'tanggal_berakhir' => $this->tanggal_berakhir ? $this->tanggal_berakhir->format('Y-m-d') : null,
            'saldo_current' => $this->saldo_current,
            'total_topup' => $this->when($this->relationLoaded('ledger'), function () {
                return $this->total_topup;
            }),
            'total_use' => $this->when($this->relationLoaded('ledger'), function () {
                return $this->total_use;
            }),
            'catatan' => $this->catatan,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
