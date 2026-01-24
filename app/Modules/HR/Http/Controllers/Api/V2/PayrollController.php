<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Modules\HR\Application\Services\PayrollService;
use App\Modules\HR\Http\Resources\PayrollPreviewResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function preview(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $data = $this->payrollService->previewPayroll(
            $request->karyawan_id,
            $request->bulan,
            $request->tahun
        );

        return ApiResponse::ok(
            new PayrollPreviewResource($data),
            'Payroll preview generated successfully'
        );
    }
}
