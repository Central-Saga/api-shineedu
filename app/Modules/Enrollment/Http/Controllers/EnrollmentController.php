<?php

namespace App\Modules\Enrollment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Enrollment\Application\Services\EnrollmentService;
use App\Modules\Enrollment\Http\Requests\StoreEnrollmentRequest;
use App\Modules\Enrollment\Http\Requests\UpdateEnrollmentRequest;
use App\Modules\Enrollment\Http\Resources\EnrollmentResource;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    protected $service;

    public function __construct(EnrollmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        // Permission check handled by route middleware usually, but good to have backup or authorize resource
        // $this->authorize('viewAny', Enrollment::class);

        $enrollments = $this->service->list($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List Enrollments retrieved successfully',
            'data' => EnrollmentResource::collection($enrollments),
            'meta' => [
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
            ],
        ]);
    }

    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        $enrollment = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Enrollment created successfully',
            'data' => new EnrollmentResource($enrollment),
        ], 201);
    }

    public function show(Enrollment $enrollment): JsonResponse
    {
        $enrollment = $this->service->show($enrollment);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment details retrieved successfully',
            'data' => new EnrollmentResource($enrollment),
        ]);
    }

    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment): JsonResponse
    {
        $enrollment = $this->service->update($enrollment, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Enrollment updated successfully',
            'data' => new EnrollmentResource($enrollment),
        ]);
    }

    public function destroy(Enrollment $enrollment): JsonResponse
    {
        $this->service->delete($enrollment);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment deleted successfully',
        ]);
    }

    public function updateRegistrationFeeStatus(Request $request, Enrollment $enrollment): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:UNPAID,PAID,WAIVED'],
            'due_date' => ['nullable', 'date'],
        ]);

        $enrollment = $this->service->updateRegistrationFeeStatus($enrollment, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Registration fee status updated successfully',
            'data' => new EnrollmentResource($enrollment),
        ]);
    }
}
