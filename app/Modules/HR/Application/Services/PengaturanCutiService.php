<?php

namespace App\Modules\HR\Application\Services;

use App\Modules\HR\Domain\Models\PengaturanCutiRules;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PengaturanCutiService
{
    /**
     * Get list with filters and pagination
     */
    public function getList(array $params): LengthAwarePaginator
    {
        $query = PengaturanCutiRules::query();

        // Search? Usually not relevant for config, but generic search could search enum values
        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->where('kategori_karyawan', 'like', "%{$keyword}%")
                    ->orWhere('jenis', 'like', "%{$keyword}%");
            });
        }

        // Filters
        if (! empty($params['kategori_karyawan'] ?? null)) {
            $query->where('kategori_karyawan', $params['kategori_karyawan']);
        }
        if (! empty($params['subtipe_kontrak'] ?? null)) {
            $query->where('subtipe_kontrak', $params['subtipe_kontrak']);
        }
        if (! empty($params['jenis'] ?? null)) {
            $query->where('jenis', $params['jenis']);
        }
        if (isset($params['aktif'])) {
            $query->where('aktif', filter_var($params['aktif'], FILTER_VALIDATE_BOOLEAN));
        }

        // Sort
        $sortWhitelist = [
            'id',
            'kategori_karyawan',
            'jenis',
            'created_at',
            'updated_at'
        ];
        $sortBy = in_array($params['sort_by'] ?? null, $sortWhitelist, true)
            ? $params['sort_by']
            : 'created_at';
        $sortDir = in_array(strtolower((string) ($params['sort_dir'] ?? '')), ['asc', 'desc'], true)
            ? strtolower((string) $params['sort_dir'])
            : 'desc';

        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) ($params['per_page'] ?? 15);
        $perPage = min(max($perPage, 1), 100);

        return $query->paginate($perPage);
    }

    /**
     * Store new rule
     */
    public function store(array $data): PengaturanCutiRules
    {
        return PengaturanCutiRules::create($data);
    }

    /**
     * Update existing rule
     */
    public function update(PengaturanCutiRules $rule, array $data): PengaturanCutiRules
    {
        $rule->update($data);
        return $rule->fresh();
    }

    /**
     * Delete rule
     */
    public function delete(PengaturanCutiRules $rule): void
    {
        $rule->delete();
    }

    /**
     * Find active rule for specific criteria
     * Used by CutiService
     */
    public function findRule(string $kategori, ?string $subtipe, string $jenis): ?PengaturanCutiRules
    {
        return PengaturanCutiRules::query()
            ->where('aktif', true)
            ->whereRaw('LOWER(kategori_karyawan) = ?', [strtolower($kategori)])
            ->whereRaw('LOWER(jenis) = ?', [strtolower($jenis)])
            ->where(function ($q) use ($subtipe) {
                if ($subtipe) {
                    $q->whereRaw('LOWER(subtipe_kontrak) = ?', [strtolower($subtipe)])
                        ->orWhereNull('subtipe_kontrak');
                } else {
                    $q->whereNull('subtipe_kontrak');
                }
            })
            ->orderByRaw('subtipe_kontrak IS NULL ASC') // Prefer exact match (non-null) first
            ->first();
    }
}
