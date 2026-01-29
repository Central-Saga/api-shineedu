<?php

namespace App\Modules\Finance\Application\Services;

use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Enrollment\Domain\Models\PaketMurid;
use App\Modules\Enrollment\Domain\Models\PaketMuridLedger;
use App\Modules\Finance\Domain\Models\KasTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaketTopupService
{
    protected KasTransaksiService $kasTransaksiService;

    public function __construct(KasTransaksiService $kasTransaksiService)
    {
        $this->kasTransaksiService = $kasTransaksiService;
    }

    /**
     * Pay registration fee for an enrollment
     * This also tops up the initial package (pendaftaran + paket pertama)
     *
     * @param Enrollment $enrollment
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function payRegistrationFee(Enrollment $enrollment, array $data): array
    {
        // Check idempotency first
        if (!empty($data['idempotency_key'])) {
            $existing = KasTransaksi::findByIdempotencyKey($data['idempotency_key']);
            if ($existing) {
                Log::info("Registration fee already paid (idempotency), returning existing transaction", [
                    'enrollment_id' => $enrollment->id,
                    'transaction_id' => $existing->id,
                ]);
                return [
                    'transaction' => $existing,
                    'enrollment' => $enrollment->fresh(),
                    'is_duplicate' => true,
                ];
            }
        }

        // Check if already paid
        if ($enrollment->biaya_pendaftaran_status === 'PAID' && $enrollment->registration_fee_transaction_id) {
            $existingTransaction = KasTransaksi::find($enrollment->registration_fee_transaction_id);
            return [
                'transaction' => $existingTransaction,
                'enrollment' => $enrollment,
                'is_duplicate' => true,
            ];
        }

        return DB::transaction(function () use ($enrollment, $data) {
            // Get current active shift
            $currentShift = \App\Modules\Finance\Domain\Models\KasShift::open()
                ->latest('opened_at')
                ->first();

            // Prepare payment details breakdown
            $registrationFee = $enrollment->biaya_pendaftaran_amount ?? 0;
            $packagePrice = $enrollment->harga_final ?? 0;
            $paket = $enrollment->paket;

            $paymentDetails = [
                'items' => [
                    [
                        'description' => 'Biaya Pendaftaran',
                        'amount' => $registrationFee,
                    ],
                    [
                        'description' => 'Paket ' . ($paket->nama ?? 'N/A'),
                        'amount' => $packagePrice,
                        'quantity' => $paket->pertemuan_per_bulan ?? 0,
                        'unit' => 'pertemuan',
                    ],
                ],
                'total' => $data['amount'],
            ];

            // Create kas transaksi for registration fee
            $transaction = KasTransaksi::create([
                'receipt_number' => KasTransaksi::generateReceiptNumber(),
                'tanggal' => $data['tanggal'] ?? now(),
                'type' => KasTransaksi::TYPE_IN,
                'amount' => $data['amount'],
                'metode' => $data['metode'],
                'kategori' => KasTransaksi::KATEGORI_BIAYA_PENDAFTARAN,
                'keterangan' => $data['keterangan'] ?? 'Pembayaran biaya pendaftaran + paket pertama',
                'payment_details' => $paymentDetails,
                'pihak' => $enrollment->murid->nama_lengkap ?? null,
                'reference_type' => KasTransaksi::REF_ENROLLMENT_FEE,
                'reference_id' => $enrollment->id,
                'external_ref' => $data['external_ref'] ?? null,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'shift_id' => $currentShift?->id, // Link to active shift
                'created_by' => auth()->id(),
            ]);

            // Handle file upload if present
            if (!empty($data['bukti_file'])) {
                $transaction->addMedia($data['bukti_file'])
                    ->toMediaCollection('payment_proof');
            }

            // Update enrollment status
            $enrollment->update([
                'biaya_pendaftaran_status' => 'PAID',
                'registration_fee_transaction_id' => $transaction->id,
            ]);

            // IMPORTANT: Also topup the initial paket_murid with pertemuan
            $paketMurid = PaketMurid::where('enrollment_id', $enrollment->id)
                ->first();

            if ($paketMurid) {
                $paket = $paketMurid->paket;
                $topupQty = $paket?->pertemuan_per_bulan ?? 4;

                // Create initial TOPUP ledger entry
                PaketMuridLedger::create([
                    'paket_murid_id' => $paketMurid->id,
                    'tanggal' => now(),
                    'type' => PaketMuridLedger::TYPE_TOPUP,
                    'qty' => $topupQty,
                    'reference_type' => PaketMuridLedger::REF_PAYMENT,
                    'reference_id' => $transaction->id,
                    'reason' => 'Saldo awal dari pembayaran pendaftaran + ' . ($paket?->nama ?? 'paket'),
                    'created_by' => auth()->id(),
                ]);

                Log::info("Initial package topup created", [
                    'enrollment_id' => $enrollment->id,
                    'paket_murid_id' => $paketMurid->id,
                    'topup_qty' => $topupQty,
                ]);
            }

            Log::info("Registration fee paid", [
                'enrollment_id' => $enrollment->id,
                'transaction_id' => $transaction->id,
                'amount' => $data['amount'],
            ]);

            return [
                'transaction' => $transaction,
                'enrollment' => $enrollment->fresh(),
                'paket_murid' => $paketMurid?->fresh(),
                'is_duplicate' => false,
            ];
        });
    }

    /**
     * Pay for package topup (creates kas transaksi + ledger TOPUP)
     *
     * @param Enrollment $enrollment
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function payPackageTopup(Enrollment $enrollment, array $data): array
    {
        // Check idempotency first
        if (!empty($data['idempotency_key'])) {
            $existing = KasTransaksi::findByIdempotencyKey($data['idempotency_key']);
            if ($existing) {
                Log::info("Package topup already processed (idempotency), returning existing transaction", [
                    'enrollment_id' => $enrollment->id,
                    'transaction_id' => $existing->id,
                ]);

                // Find the associated ledger entry
                $ledger = PaketMuridLedger::where('reference_type', PaketMuridLedger::REF_PAYMENT)
                    ->where('reference_id', $existing->id)
                    ->first();

                return [
                    'transaction' => $existing,
                    'paket_murid' => $ledger?->paketMurid,
                    'ledger' => $ledger,
                    'is_duplicate' => true,
                ];
            }
        }

        return DB::transaction(function () use ($enrollment, $data) {
            // Get current active shift
            $currentShift = \App\Modules\Finance\Domain\Models\KasShift::open()
                ->latest('opened_at')
                ->first();

            // Determine paket_murid
            $paketMurid = null;
            $paket = null;

            if (!empty($data['paket_murid_id'])) {
                // Use existing paket_murid
                $paketMurid = PaketMurid::where('id', $data['paket_murid_id'])
                    ->where('enrollment_id', $enrollment->id)
                    ->firstOrFail();
                $paket = $paketMurid->paket;
            } elseif (!empty($data['paket_id'])) {
                // Create new paket_murid from master paket
                $paket = Paket::findOrFail($data['paket_id']);
                $paketMurid = PaketMurid::create([
                    'enrollment_id' => $enrollment->id,
                    'paket_id' => $paket->id,
                    'status' => 'AKTIF',
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_berakhir' => null,
                    'catatan' => $data['catatan'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            } else {
                throw new \InvalidArgumentException('Either paket_murid_id or paket_id is required');
            }

            // Determine topup qty
            $topupQty = $data['topup_qty'] ?? $paket?->pertemuan_per_bulan ?? 4;

            // Prepare payment details
            $paymentDetails = [
                'items' => [
                    [
                        'description' => 'Paket ' . ($paket?->nama ?? 'N/A'),
                        'amount' => $data['amount'],
                        'quantity' => $topupQty,
                        'unit' => 'pertemuan',
                    ],
                ],
                'total' => $data['amount'],
            ];

            // Create kas transaksi
            $transaction = KasTransaksi::create([
                'receipt_number' => KasTransaksi::generateReceiptNumber(),
                'tanggal' => $data['tanggal'] ?? now(),
                'type' => KasTransaksi::TYPE_IN,
                'amount' => $data['amount'],
                'metode' => $data['metode'],
                'kategori' => KasTransaksi::KATEGORI_PEMBAYARAN_PAKET,
                'keterangan' => $data['keterangan'] ?? 'Pembayaran paket ' . ($paket?->nama ?? 'N/A'),
                'payment_details' => $paymentDetails,
                'pihak' => $enrollment->murid?->nama_lengkap ?? null,
                'reference_type' => KasTransaksi::REF_PAKET_TOPUP,
                'reference_id' => $paketMurid->id,
                'external_ref' => $data['external_ref'] ?? null,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'shift_id' => $currentShift?->id, // Link to active shift
                'created_by' => auth()->id(),
            ]);

            // Handle file upload if present
            if (!empty($data['bukti_file'])) {
                $transaction->addMedia($data['bukti_file'])
                    ->toMediaCollection('payment_proof');
            }

            // Create TOPUP ledger entry
            // The unique constraint (reference_type, reference_id, type) prevents double topup
            $ledger = PaketMuridLedger::create([
                'paket_murid_id' => $paketMurid->id,
                'tanggal' => now(),
                'type' => PaketMuridLedger::TYPE_TOPUP,
                'qty' => $topupQty,
                'reference_type' => PaketMuridLedger::REF_PAYMENT,
                'reference_id' => $transaction->id,
                'reason' => 'Topup dari pembayaran #' . $transaction->id,
                'created_by' => auth()->id(),
            ]);

            Log::info("Package topup completed", [
                'enrollment_id' => $enrollment->id,
                'paket_murid_id' => $paketMurid->id,
                'transaction_id' => $transaction->id,
                'topup_qty' => $topupQty,
            ]);

            // Reload relationships
            $paketMurid->load('paket', 'ledger');

            return [
                'transaction' => $transaction,
                'paket_murid' => $paketMurid,
                'ledger' => $ledger,
                'is_duplicate' => false,
            ];
        });
    }

    /**
     * Get saldo and recent transactions for an enrollment
     *
     * @param Enrollment $enrollment
     * @return array
     */
    public function getSaldoAndTransactions(Enrollment $enrollment): array
    {
        // Get all paket_murid with saldo
        $paketMurids = PaketMurid::where('enrollment_id', $enrollment->id)
            ->where('status', 'AKTIF')
            ->with(['paket', 'ledger'])
            ->get();

        $saldo = $paketMurids->map(function ($pm) {
            return [
                'paket_murid_id' => $pm->id,
                'paket_id' => $pm->paket_id,
                'paket_nama' => $pm->paket?->nama ?? 'N/A',
                'status' => $pm->status,
                'saldo_current' => $pm->saldo_current,
                'total_topup' => $pm->total_topup,
                'total_use' => $pm->total_use,
                'tanggal_mulai' => $pm->tanggal_mulai?->format('Y-m-d'),
                'tanggal_berakhir' => $pm->tanggal_berakhir?->format('Y-m-d'),
            ];
        })->toArray();

        // Get recent transactions for this enrollment
        // 1. Enrollment fee transactions
        // 2. Paket topup transactions (via paket_murid)
        $paketMuridIds = $paketMurids->pluck('id')->toArray();

        $transactions = KasTransaksi::where('type', KasTransaksi::TYPE_IN)
            ->where(function ($query) use ($enrollment, $paketMuridIds) {
                // Enrollment fee
                $query->where(function ($q) use ($enrollment) {
                    $q->where('reference_type', KasTransaksi::REF_ENROLLMENT_FEE)
                        ->where('reference_id', $enrollment->id);
                });
                // Paket topup
                if (!empty($paketMuridIds)) {
                    $query->orWhere(function ($q) use ($paketMuridIds) {
                        $q->where('reference_type', KasTransaksi::REF_PAKET_TOPUP)
                            ->whereIn('reference_id', $paketMuridIds);
                    });
                }
            })
            ->with('createdBy')
            ->orderBy('tanggal', 'desc')
            ->limit(20)
            ->get();

        return [
            'saldo' => $saldo,
            'transaksi_terakhir' => $transactions,
        ];
    }
}
