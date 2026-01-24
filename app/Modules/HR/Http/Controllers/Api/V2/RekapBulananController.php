<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Modules\HR\Application\Services\RekapBulananService;
use App\Modules\HR\Application\Services\PayrollService;
use App\Modules\HR\Http\Resources\RekapBulananResource;
use App\Shared\Http\Responses\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapBulananController extends Controller
{
    protected $rekapService;
    protected $payrollService;

    public function __construct(RekapBulananService $rekapService, PayrollService $payrollService)
    {
        $this->rekapService = $rekapService;
        $this->payrollService = $payrollService;
    }

    /**
     * Get Monthly Recap List
     */
    public function index(Request $request)
    {
        // 1. Validation
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2030',
        ]);

        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        // 2. Query Employees
        $query = $this->rekapService->getRekapList($bulan, $tahun, $request->all());

        // 3. Pagination
        $perPage = $request->input('per_page', 15);
        $employees = $query->paginate($perPage);

        // 4. Transform: Calculate Summary for each Employee in the current page
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $employees->getCollection()->transform(function ($employee) use ($startDate, $endDate) {
            $summary = $this->rekapService->calculateSummary($employee, $startDate, $endDate);
            $employee->setAttribute('summary', $summary);
            return $employee;
        });

        return ApiResponse::paginated(
            RekapBulananResource::collection($employees),
            $employees,
            'Rekap bulanan retrieved successfully',
            ['periode' => ['bulan' => $bulan, 'tahun' => $tahun]]
        );
    }

    public function show(Request $request, $id)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2030',
        ]);

        $employee = \App\Modules\HR\Domain\Models\Employee::findOrFail($id);

        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $summary = $this->rekapService->calculateSummary($employee, $startDate, $endDate);

        return ApiResponse::ok(
            new RekapBulananResource($employee, $summary),
            'Detail rekap retrieved successfully'
        );
    }
}
