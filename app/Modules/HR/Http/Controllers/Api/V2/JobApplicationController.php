<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Application\Services\JobApplicationService;
use App\Modules\HR\Domain\Models\JobApplication;
use App\Modules\HR\Http\Requests\StoreJobApplicationRequest;
use App\Modules\HR\Http\Requests\UpdateJobApplicationRequest;
use App\Modules\HR\Http\Resources\JobApplicationResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApplicationController
{
    public function __construct(
        protected JobApplicationService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getList($request->all());

        return ApiResponse::paginated(
            JobApplicationResource::collection($data),
            $data,
            'Data lamaran berhasil diambil'
        );
    }

    public function store(StoreJobApplicationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['job_vacancy_id'] = $data['position_id'] ?? $data['job_vacancy_id'] ?? null;
        unset($data['position_id']);

        $application = $this->service->create($data);

        return ApiResponse::created(
            new JobApplicationResource($application),
            'Lamaran berhasil dikirim'
        );
    }

    public function show(JobApplication $job_application): JsonResponse
    {
        $job_application->load('jobVacancy');

        return ApiResponse::ok(
            new JobApplicationResource($job_application),
            'Detail lamaran berhasil diambil'
        );
    }

    public function update(UpdateJobApplicationRequest $request, JobApplication $job_application): JsonResponse
    {
        $data = $request->validated();
        if (isset($data['position_id'])) {
            $data['job_vacancy_id'] = $data['position_id'];
            unset($data['position_id']);
        }

        $updated = $this->service->update($job_application, $data);

        return ApiResponse::ok(
            new JobApplicationResource($updated),
            'Data lamaran berhasil diperbarui'
        );
    }

    public function destroy(JobApplication $job_application): JsonResponse
    {
        $this->service->delete($job_application);

        return ApiResponse::ok(null, 'Lamaran berhasil dihapus');
    }

    /**
     * Public: pantau status lamaran berdasarkan tracking_code + email (tanpa auth).
     */
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_code' => 'required|string|max:100',
            'email' => 'required|email',
        ]);

        $application = JobApplication::query()
            ->with('jobVacancy')
            ->where('tracking_code', $request->input('tracking_code'))
            ->where('email', $request->input('email'))
            ->first();

        if (! $application) {
            return ApiResponse::notFound('Lamaran tidak ditemukan. Pastikan ID Aplikasi dan email sesuai.');
        }

        return ApiResponse::ok([
            'tracking_code' => $application->tracking_code,
            'status' => $application->status,
            'position' => $application->jobVacancy ? [
                'title' => $application->jobVacancy->title,
                'location' => $application->jobVacancy->location,
            ] : null,
            'created_at' => $application->created_at?->toIso8601String(),
            'updated_at' => $application->updated_at?->toIso8601String(),
        ], 'Status lamaran berhasil diambil');
    }
}
