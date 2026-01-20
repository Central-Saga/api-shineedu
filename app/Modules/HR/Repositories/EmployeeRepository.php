<?php

namespace App\Modules\HR\Repositories;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    protected const SORT_WHITELIST = [
        'kode_karyawan',
        'status',
        'gaji_pokok',
        'created_at',
        'updated_at',
    ];

    public function paginate(array $params): LengthAwarePaginator
    {
        $query = Employee::query()->with('user');

        // Search: kode_karyawan OR user name/email
        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('kode_karyawan', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function (Builder $uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        // Filters
        if (! empty($params['status'] ?? null)) {
            $query->where('status', $params['status']);
        }
        if (! empty($params['kategori_karyawan'] ?? null)) {
            $query->where('kategori_karyawan', $params['kategori_karyawan']);
        }
        if (! empty($params['subtipe_kontrak'] ?? null)) {
            $query->where('subtipe_kontrak', $params['subtipe_kontrak']);
        }
        if (! empty($params['tipe_gaji'] ?? null)) {
            $query->where('tipe_gaji', $params['tipe_gaji']);
        }

        // Sort
        $sortBy = in_array($params['sort_by'] ?? null, self::SORT_WHITELIST, true)
            ? $params['sort_by']
            : 'created_at';
        $sortDir = in_array(strtolower((string) ($params['sort_dir'] ?? '')), ['asc', 'desc'], true)
            ? strtolower((string) $params['sort_dir'])
            : 'desc';
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int) ($params['per_page'] ?? 15);
        $perPage = min(max($perPage, 1), 100);

        return $query->paginate($perPage)->withQueryString();
    }
}
