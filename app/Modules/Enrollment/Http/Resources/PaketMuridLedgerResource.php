<?php

namespace App\Modules\Enrollment\Http\Resources;

use App\Modules\Identity\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PaketMuridLedgerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal->toIso8601String(),
            'type' => $this->type,
            'qty' => $this->qty,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'reason' => $this->reason,
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'paket_murid' => new PaketMuridResource($this->whenLoaded('paketMurid')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
