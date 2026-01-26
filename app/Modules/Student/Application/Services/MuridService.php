<?php

namespace App\Modules\Student\Application\Services;

use App\Modules\Student\Domain\Models\Murid;
use Illuminate\Pagination\LengthAwarePaginator;

class MuridService
{
    /**
     * Get list of Murid with filters and pagination.
     */
    public function getList(array $params): LengthAwarePaginator
    {
        $query = Murid::query()->with(['jenjang']);

        // Search
        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', "%{$keyword}%")
                    ->orWhere('kode_murid', 'like', "%{$keyword}%")
                    ->orWhere('no_hp', 'like', "%{$keyword}%");
            });
        }

        // Filters
        if (! empty($params['status'] ?? null)) {
            $query->where('status', $params['status']);
        }

        if (! empty($params['jenjang_id'] ?? null)) {
            $query->where('jenjang_id', $params['jenjang_id']);
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDir = $params['sort_dir'] ?? 'desc';
        // Allowed sort columns protection could be added here
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($params['per_page'] ?? 15);
    }

    /**
     * Create a new Murid.
     */
    public function create(array $data): Murid
    {
        if (empty($data['kode_murid'])) {
            $dateSource = !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : date('Y-m-d');
            $timestamp = strtotime($dateSource);
            $ddmmyy = date('dmy', $timestamp);
            $random = rand(1000, 9999);

            $data['kode_murid'] = $ddmmyy . $random;
        }

        return Murid::create($data);
    }

    /**
     * Show a Murid details.
     */
    public function show(Murid $murid): Murid
    {
        return $murid->load('jenjang');
    }

    /**
     * Update a Murid.
     */
    public function update(Murid $murid, array $data): Murid
    {
        $murid->update($data);
        return $murid;
    }

    /**
     * Delete a Murid (Soft Delete).
     */
    public function delete(Murid $murid): void
    {
        $murid->delete();
    }
}
