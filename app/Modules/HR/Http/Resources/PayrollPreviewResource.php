<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollPreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Expecting array structure from PayrollService::previewPayroll

        return [
            'employee' => [
                'id' => $this['employee']->id,
                'nama' => $this['employee']->user->name ?? 'Unknown',
                'kode_karyawan' => $this['employee']->kode_karyawan,
                'tipe_gaji' => $this['employee']->tipe_gaji,
            ],
            'periode' => $this['periode'],
            'komponen' => [
                'gaji_pokok' => $this['komponen']['gaji_pokok'],
                'fee_sesi' => $this['komponen']['fee_sesi'],
                'potongan' => $this['komponen']['potongan'],
                'total_potongan' => $this['totals']['total_potongan'],
            ],
            'totals' => [
                'total_pendapatan' => $this['totals']['total_pendapatan'],
                'gaji_bersih' => $this['totals']['gaji_bersih'],
            ],
        ];
    }
}
