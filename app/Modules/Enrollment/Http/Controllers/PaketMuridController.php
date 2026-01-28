<?php

namespace App\Modules\Enrollment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Enrollment\Application\Services\SaldoPertemuanService;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Enrollment\Domain\Models\PaketMurid;
use App\Modules\Enrollment\Domain\Models\PaketMuridLedger;
use App\Modules\Enrollment\Http\Requests\AdjustPaketMuridRequest;
use App\Modules\Enrollment\Http\Requests\StorePaketMuridRequest;
use App\Modules\Enrollment\Http\Resources\PaketMuridLedgerResource;
use App\Modules\Enrollment\Http\Resources\PaketMuridResource;
use Illuminate\Http\Request;

class PaketMuridController extends Controller
{
    protected $saldoService;

    public function __construct(SaldoPertemuanService $saldoService)
    {
        $this->saldoService = $saldoService;
    }

    /**
     * Get all active packages for an enrollment
     */
    public function index(Enrollment $enrollment)
    {
        $paketMurids = $enrollment->paketMurid()
            ->with('paket')
            ->where('status', 'AKTIF')
            ->orderBy('created_at', 'desc')
            ->get();

        return PaketMuridResource::collection($paketMurids);
    }

    /**
     * Create a new package for a student (enrollment)
     */
    public function store(StorePaketMuridRequest $request, Enrollment $enrollment)
    {
        $paketMurid = $this->saldoService->createPaketMurid(
            $enrollment,
            $request->paket_id,
            $request->validated()
        );

        return new PaketMuridResource($paketMurid);
    }

    /**
     * Get saldo summary for an enrollment
     */
    public function getSaldo(Enrollment $enrollment)
    {
        $saldoData = $this->saldoService->getSaldoByEnrollment($enrollment);
        return response()->json(['data' => $saldoData]);
    }

    /**
     * Get ledger history for a package
     */
    public function getLedger(Request $request, PaketMurid $paketMurid)
    {
        $perPage = $request->input('per_page', 20);

        $ledger = $paketMurid->ledger()
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return PaketMuridLedgerResource::collection($ledger);
    }

    /**
     * Admin manual adjustment or expire credits
     */
    public function adjust(AdjustPaketMuridRequest $request, PaketMurid $paketMurid)
    {
        $result = $this->saldoService->adminAdjust(
            $paketMurid,
            $request->type,
            $request->qty,
            $request->reason
        );

        return response()->json([
            'message' => 'Adjustment successful',
            'data' => [
                'saldo_current' => $result['saldo_current'],
                'ledger' => new PaketMuridLedgerResource($result['ledger']),
            ]
        ]);
    }
}
