<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Enrollment\Http\Resources\PaketMuridResource;
use App\Modules\Finance\Application\Services\KasTransaksiService;
use App\Modules\Finance\Application\Services\PaketTopupService;
use App\Modules\Finance\Http\Requests\PayPackageTopupRequest;
use App\Modules\Finance\Http\Requests\PayRegistrationFeeRequest;
use App\Modules\Finance\Http\Requests\StoreKasTransaksiRequest;
use App\Modules\Finance\Http\Resources\KasTransaksiResource;
use Illuminate\Http\Request;

class KasTransaksiController extends Controller
{
    protected KasTransaksiService $kasTransaksiService;
    protected PaketTopupService $paketTopupService;

    public function __construct(
        KasTransaksiService $kasTransaksiService,
        PaketTopupService $paketTopupService
    ) {
        $this->kasTransaksiService = $kasTransaksiService;
        $this->paketTopupService = $paketTopupService;
    }

    /**
     * List cash transactions with filters
     *
     * GET /api/v2/kas/transaksi
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'q',
            'type',
            'kategori',
            'tanggal_from',
            'tanggal_to',
            'reference_type',
            'reference_id',
            'per_page',
            'page',
        ]);

        $transactions = $this->kasTransaksiService->getList($filters);

        return KasTransaksiResource::collection($transactions);
    }

    /**
     * Create a generic cash transaction (IN or OUT)
     *
     * POST /api/v2/kas/transaksi
     */
    public function store(StoreKasTransaksiRequest $request)
    {
        $data = $request->validated();

        // Add the uploaded file if present
        if ($request->hasFile('bukti_file')) {
            $data['bukti_file'] = $request->file('bukti_file');
        }

        $transaction = $this->kasTransaksiService->createTransaction($data);

        return new KasTransaksiResource($transaction);
    }

    /**
     * Pay registration fee for an enrollment
     *
     * POST /api/v2/enrollments/{enrollment}/pay-registration-fee
     */
    public function payRegistrationFee(PayRegistrationFeeRequest $request, Enrollment $enrollment)
    {
        try {
            $data = $request->validated();

            // Add the uploaded file if present
            if ($request->hasFile('bukti_file')) {
                $data['bukti_file'] = $request->file('bukti_file');
            }

            $result = $this->paketTopupService->payRegistrationFee($enrollment, $data);

            $status = $result['is_duplicate'] ? 200 : 201;

            return response()->json([
                'message' => $result['is_duplicate']
                    ? 'Pembayaran sudah tercatat sebelumnya'
                    : 'Pembayaran biaya pendaftaran berhasil',
                'data' => [
                    'transaction' => new KasTransaksiResource($result['transaction']),
                    'enrollment' => [
                        'id' => $result['enrollment']->id,
                        'biaya_pendaftaran_status' => $result['enrollment']->biaya_pendaftaran_status,
                        'registration_fee_transaction_id' => $result['enrollment']->registration_fee_transaction_id,
                    ],
                ],
                'is_duplicate' => $result['is_duplicate'],
            ], $status);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memproses pembayaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pay for package topup (creates transaction + ledger TOPUP)
     *
     * POST /api/v2/enrollments/{enrollment}/pay-package-topup
     */
    public function payPackageTopup(PayPackageTopupRequest $request, Enrollment $enrollment)
    {
        try {
            $data = $request->validated();

            // Add the uploaded file if present
            if ($request->hasFile('bukti_file')) {
                $data['bukti_file'] = $request->file('bukti_file');
            }

            $result = $this->paketTopupService->payPackageTopup($enrollment, $data);

            $status = $result['is_duplicate'] ? 200 : 201;

            return response()->json([
                'message' => $result['is_duplicate']
                    ? 'Pembayaran sudah tercatat sebelumnya'
                    : 'Pembayaran paket dan topup saldo berhasil',
                'data' => [
                    'transaction' => new KasTransaksiResource($result['transaction']),
                    'paket_murid' => $result['paket_murid']
                        ? new PaketMuridResource($result['paket_murid'])
                        : null,
                    'ledger' => $result['ledger'] ? [
                        'id' => $result['ledger']->id,
                        'type' => $result['ledger']->type,
                        'qty' => $result['ledger']->qty,
                        'reason' => $result['ledger']->reason,
                    ] : null,
                ],
                'is_duplicate' => $result['is_duplicate'],
            ], $status);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memproses pembayaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get saldo and recent transactions for an enrollment
     *
     * GET /api/v2/enrollments/{enrollment}/saldo-dan-transaksi
     */
    public function getSaldoAndTransactions(Enrollment $enrollment)
    {
        $result = $this->paketTopupService->getSaldoAndTransactions($enrollment);

        return response()->json([
            'data' => [
                'saldo' => $result['saldo'],
                'transaksi_terakhir' => KasTransaksiResource::collection($result['transaksi_terakhir']),
            ],
        ]);
    }
}
