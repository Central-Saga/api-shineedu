<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Application\Services\JobVacancyService;
use App\Modules\HR\Domain\Models\JobVacancy;
use App\Modules\HR\Http\Requests\StoreJobVacancyRequest;
use App\Modules\HR\Http\Requests\UpdateJobVacancyRequest;
use App\Modules\HR\Http\Resources\JobVacancyResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobVacancyController
{
    public function __construct(
        protected JobVacancyService $service
    ) {}

    /**
     * Public: list active job vacancies (simple, for landing dropdown / listing).
     */
    public function index(): JsonResponse
    {
        $items = JobVacancy::where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title', 'location', 'employment_type']);

        return ApiResponse::ok(
            $items->map(fn ($v) => [
                'id' => $v->id,
                'title' => $v->title,
                'location' => $v->location,
                'employment_type' => $v->employment_type,
            ]),
            'Daftar posisi berhasil diambil'
        );
    }

    /**
     * Protected: paginated list for admin dashboard.
     */
    public function list(Request $request): JsonResponse
    {
        $data = $this->service->getList($request->all());

        return ApiResponse::paginated(
            JobVacancyResource::collection($data),
            $data,
            'Daftar lowongan berhasil diambil'
        );
    }

    public function store(StoreJobVacancyRequest $request): JsonResponse
    {
        $vacancy = $this->service->create($request->validated());

        return ApiResponse::created(
            new JobVacancyResource($vacancy),
            'Lowongan berhasil dibuat'
        );
    }

    public function show(JobVacancy $job_vacancy): JsonResponse
    {
        return ApiResponse::ok(
            new JobVacancyResource($job_vacancy),
            'Detail lowongan berhasil diambil'
        );
    }

    public function update(UpdateJobVacancyRequest $request, JobVacancy $job_vacancy): JsonResponse
    {
        $updated = $this->service->update($job_vacancy, $request->validated());

        return ApiResponse::ok(
            new JobVacancyResource($updated),
            'Lowongan berhasil diperbarui'
        );
    }

    public function destroy(JobVacancy $job_vacancy): JsonResponse
    {
        $this->service->delete($job_vacancy);

        return ApiResponse::ok(null, 'Lowongan berhasil dihapus');
    }
}
