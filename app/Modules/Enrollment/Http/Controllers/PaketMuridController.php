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

    public function getLedger(Request $request, ?PaketMurid $paketMurid = null)
    {
        $perPage = $request->input('per_page', 20);

        // Determine if we should aggregate
        $isAggregated = $request->boolean('all') || ($request->has('enrollment_id') && $request->has('paket_id'));

        $enrollmentId = $request->input('enrollment_id');
        $paketId = $request->input('paket_id');

        // If we have a specific PaketMurid and were told to aggregate,
        // use its enrollment and paket type as defaults.
        if ($paketMurid && $isAggregated) {
            $enrollmentId = $enrollmentId ?: $paketMurid->enrollment_id;
            $paketId = $paketId ?: $paketMurid->paket_id;
        }

        if ($isAggregated && $enrollmentId && $paketId) {
            $ledger = PaketMuridLedger::whereHas('paketMurid', function ($q) use ($enrollmentId, $paketId) {
                $q->where('enrollment_id', $enrollmentId)
                    ->where('paket_id', $paketId);
            })
                ->with(['paketMurid.paket', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return PaketMuridLedgerResource::collection($ledger);
        }

        if (!$paketMurid) {
            return response()->json(['message' => 'Paket Murid not found'], 404);
        }

        $ledger = $paketMurid->ledger()
            ->with(['paketMurid.paket', 'createdBy'])
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
