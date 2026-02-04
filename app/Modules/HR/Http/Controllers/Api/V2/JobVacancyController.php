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
use Illuminate\Support\Facades\Log;

class JobVacancyController
{
    public function __construct(
        protected JobVacancyService $service
    ) {}

    /**
     * Public: list active job vacancies with full detail (for landing page list + detail modal).
     */
    public function index(): JsonResponse
    {
        try {
            $items = JobVacancy::where('is_active', true)
                ->orderBy('title')
                ->get();

            return ApiResponse::ok(
                JobVacancyResource::collection($items),
                'Daftar posisi berhasil diambil'
            );
        } catch (\Throwable $e) {
            Log::error('JobVacancyControllesr@index failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return ApiResponse::serverError(
                'Gagal mengambil daftar lowongan.',
                $e->getMessage()
            );
        }
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
