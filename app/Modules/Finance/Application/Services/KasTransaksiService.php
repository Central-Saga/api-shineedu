<?php

namespace App\Modules\Finance\Application\Services;

use App\Modules\Finance\Domain\Models\KasTransaksi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class KasTransaksiService
{
    /**
     * Create a new cash transaction
     *
     * @param array $data
     * @return KasTransaksi
     */
    public function createTransaction(array $data): KasTransaksi
    {
        // Check idempotency first
        if (!empty($data['idempotency_key'])) {
            $existing = KasTransaksi::findByIdempotencyKey($data['idempotency_key']);
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($data) {
            return KasTransaksi::create([
                'tanggal' => $data['tanggal'] ?? now(),
                'type' => $data['type'],
                'amount' => $data['amount'],
                'metode' => $data['metode'],
                'kategori' => $data['kategori'],
                'keterangan' => $data['keterangan'] ?? null,
                'pihak' => $data['pihak'] ?? null,
                'reference_type' => $data['reference_type'] ?? KasTransaksi::REF_MANUAL,
                'reference_id' => $data['reference_id'] ?? null,
                'external_ref' => $data['external_ref'] ?? null,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Get paginated list of transactions with filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getList(array $filters = []): LengthAwarePaginator
    {
        $query = KasTransaksi::query()
            ->with('createdBy')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        // Filter by type
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by kategori
        if (!empty($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        // Filter by date range
        if (!empty($filters['tanggal_from'])) {
            $query->where('tanggal', '>=', $filters['tanggal_from']);
        }
        if (!empty($filters['tanggal_to'])) {
            $query->where('tanggal', '<=', $filters['tanggal_to'] . ' 23:59:59');
        }

        // Search by keterangan, pihak, external_ref
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qb) use ($q) {
                $qb->where('keterangan', 'like', "%{$q}%")
                    ->orWhere('pihak', 'like', "%{$q}%")
                    ->orWhere('external_ref', 'like', "%{$q}%");
            });
        }

        // Filter by reference
        if (!empty($filters['reference_type'])) {
            $query->where('reference_type', $filters['reference_type']);
        }
        if (!empty($filters['reference_id'])) {
            $query->where('reference_id', $filters['reference_id']);
        }

        $perPage = $filters['per_page'] ?? 20;

        return $query->paginate($perPage);
    }

    /**
     * Get transactions by enrollment (via reference)
     *
     * @param int $enrollmentId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByEnrollment(int $enrollmentId, int $limit = 10)
    {
        return KasTransaksi::where('type', KasTransaksi::TYPE_IN)
            ->where(function ($query) use ($enrollmentId) {
                $query->where(function ($q) use ($enrollmentId) {
                    $q->where('reference_type', KasTransaksi::REF_ENROLLMENT_FEE)
                        ->where('reference_id', $enrollmentId);
                });
                // Also include paket_topup transactions for this enrollment
                // These will be linked via paket_murid which has enrollment_id
            })
            ->with('createdBy')
            ->orderBy('tanggal', 'desc')
            ->limit($limit)
            ->get();
    }
}
