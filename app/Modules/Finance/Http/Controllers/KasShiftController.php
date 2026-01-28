<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Application\Services\KasShiftService;
use App\Modules\Finance\Domain\Models\KasShift;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KasShiftController extends Controller
{
    public function __construct(
        private KasShiftService $shiftService
    ) {}

    /**
     * List all shifts with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = KasShift::with(['openedByUser', 'closedByUser'])
            ->latest('opened_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('opened_at', $request->date);
        }

        $shifts = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $shifts->items(),
            'meta' => [
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
                'per_page' => $shifts->perPage(),
                'total' => $shifts->total(),
            ],
        ]);
    }

    /**
     * Get the current open shift
     */
    public function current(): JsonResponse
    {
        $shift = $this->shiftService->getCurrentShift();

        if (!$shift) {
            return response()->json([
                'data' => null,
                'message' => 'Tidak ada shift aktif',
            ]);
        }

        // Force reload to get latest transactions
        $shift = $shift->fresh(['transaksis', 'openedByUser', 'closedByUser']);
        $summary = $this->shiftService->getShiftSummary($shift);

        return response()->json([
            'data' => $summary,
        ]);
    }

    /**
     * Open a new shift
     */
    public function open(Request $request): JsonResponse
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ], [
            'opening_balance.required' => 'Saldo awal wajib diisi.',
            'opening_balance.min' => 'Saldo awal tidak boleh negatif.',
        ]);

        try {
            $shift = $this->shiftService->openShift((float) $request->opening_balance);
            $shift->load('openedByUser');

            return response()->json([
                'data' => $shift,
                'message' => 'Shift berhasil dibuka',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Close a shift
     */
    public function close(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ], [
            'actual_cash.required' => 'Jumlah uang kas wajib diisi.',
            'actual_cash.min' => 'Jumlah uang kas tidak boleh negatif.',
        ]);

        $shift = KasShift::findOrFail($id);

        try {
            $closedShift = $this->shiftService->closeShift(
                $shift,
                (float) $request->actual_cash,
                $request->notes
            );

            $summary = $this->shiftService->getShiftSummary($closedShift);

            return response()->json([
                'data' => $summary,
                'message' => 'Shift berhasil ditutup',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get shift summary
     */
    public function summary(int $id): JsonResponse
    {
        $shift = KasShift::findOrFail($id);
        $summary = $this->shiftService->getShiftSummary($shift);

        return response()->json([
            'data' => $summary,
        ]);
    }

    /**
     * Print shift summary (thermal receipt)
     */
    public function print(int $id)
    {
        $shift = KasShift::with(['transaksis', 'openedByUser', 'closedByUser'])->findOrFail($id);
        $summary = $this->shiftService->getShiftSummary($shift);

        return view('finance.shift-summary', [
            'shift' => $shift,
            'summary' => $summary,
        ]);
    }
}
