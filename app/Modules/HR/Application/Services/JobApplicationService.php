<?php

namespace App\Modules\HR\Application\Services;

use App\Modules\HR\Domain\Models\JobApplication;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class JobApplicationService
{
    public function getList(array $params): LengthAwarePaginator
    {
        $query = JobApplication::query()->with('jobVacancy');

        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('tracking_code', 'like', "%{$keyword}%");
            });
        }

        if (! empty($params['position_id'] ?? null) || ! empty($params['job_vacancy_id'] ?? null)) {
            $query->where('job_vacancy_id', $params['position_id'] ?? $params['job_vacancy_id']);
        }
        if (! empty($params['status'] ?? null)) {
            $query->where('status', $params['status']);
        }

        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDir = $params['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate((int) ($params['per_page'] ?? 15));
    }

    public function create(array $data): JobApplication
    {
        $trackingCode = 'JA-' . strtoupper(Str::random(8));
        while (JobApplication::where('tracking_code', $trackingCode)->exists()) {
            $trackingCode = 'JA-' . strtoupper(Str::random(8));
        }

        $application = JobApplication::create(array_merge($data, [
            'status' => 'pending',
            'tracking_code' => $trackingCode,
        ]));

        if (! empty($data['resume']) && $data['resume'] instanceof \Illuminate\Http\UploadedFile) {
            $application->addMedia($data['resume'])->toMediaCollection('resume');
        }
        if (! empty($data['cover_letter']) && $data['cover_letter'] instanceof \Illuminate\Http\UploadedFile) {
            $application->addMedia($data['cover_letter'])->toMediaCollection('cover_letter');
        }

        return $application->load('jobVacancy');
    }

    public function update(JobApplication $application, array $data): JobApplication
    {
        $application->update(collect($data)->except(['resume', 'cover_letter'])->toArray());

        if (array_key_exists('resume', $data) && $data['resume'] instanceof \Illuminate\Http\UploadedFile) {
            $application->clearMediaCollection('resume');
            $application->addMedia($data['resume'])->toMediaCollection('resume');
        }
        if (array_key_exists('cover_letter', $data) && $data['cover_letter'] instanceof \Illuminate\Http\UploadedFile) {
            $application->clearMediaCollection('cover_letter');
            $application->addMedia($data['cover_letter'])->toMediaCollection('cover_letter');
        }

        return $application->fresh('jobVacancy');
    }

    public function delete(JobApplication $application): void
    {
        $application->delete();
    }
}
