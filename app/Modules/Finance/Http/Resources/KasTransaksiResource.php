<?php

namespace App\Modules\Finance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KasTransaksiResource extends JsonResource
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
            'tanggal' => $this->tanggal?->format('Y-m-d H:i:s'),
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'metode' => $this->metode,
            'kategori' => $this->kategori,
            'keterangan' => $this->keterangan,
            'pihak' => $this->pihak,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'external_ref' => $this->external_ref,
            'idempotency_key' => $this->idempotency_key,
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
