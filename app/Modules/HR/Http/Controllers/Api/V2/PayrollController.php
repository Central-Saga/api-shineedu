<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Modules\HR\Application\Services\PayrollService;
use App\Modules\HR\Domain\Models\Payroll;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollService $service
    ) {}

    /**
     * List Generated Payrolls
     */
    public function index(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
        ]);

        $query = Payroll::query()
            ->with(['employee.user'])
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun);

        if ($q = $request->get('q')) {
            $query->whereHas('employee', function ($sub) use ($q) {
                $sub->where('kode_karyawan', 'like', "%{$q}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $data = $query->paginate($request->get('per_page', 15));

        return ApiResponse::paginated(
            $data->items(),
            $data,
            'Data payroll berhasil diambil'
        );
    }

    /**
     * Trigger Generation / Synchronization
     */
    public function generate(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
        ]);

        $results = $this->service->generateByMonth($request->bulan, $request->tahun);

        return ApiResponse::ok(
            $results,
            'Payroll berhasil digenerate/disinkronisasi untuk ' . count($results) . ' karyawan.'
        );
    }

    /**
     * Update Status (e.g. Mark as Paid)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,approved,paid,transferred',
        ]);

        $payroll = $this->service->updateStatus($id, $request->status);

        return ApiResponse::ok($payroll, 'Status payroll berhasil diperbarui');
    }

    /**
     * Show Detail
     */
    public function show($id)
    {
        $payroll = Payroll::with(['employee.user'])->findOrFail($id);
        return ApiResponse::ok($payroll, 'Detail payroll berhasil diambil');
    }
}
