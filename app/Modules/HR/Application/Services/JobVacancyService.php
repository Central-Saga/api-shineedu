<?php

namespace App\Modules\HR\Application\Services;

use App\Modules\HR\Domain\Models\JobVacancy;
use Illuminate\Pagination\LengthAwarePaginator;

class JobVacancyService
{
    public function getList(array $params): LengthAwarePaginator
    {
        $query = JobVacancy::query();

        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%")
                    ->orWhere('employment_type', 'like', "%{$keyword}%");
            });
        }

        if (isset($params['is_active']) && $params['is_active'] !== '' && $params['is_active'] !== null) {
            $query->where('is_active', (bool) $params['is_active']);
        }

        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDir = $params['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate((int) ($params['per_page'] ?? 15));
    }

    public function create(array $data): JobVacancy
    {
        return JobVacancy::create($data);
    }

    public function update(JobVacancy $vacancy, array $data): JobVacancy
    {
        $vacancy->update($data);

        return $vacancy->fresh();
    }

    public function delete(JobVacancy $vacancy): void
    {
        $vacancy->delete();
    }
}
