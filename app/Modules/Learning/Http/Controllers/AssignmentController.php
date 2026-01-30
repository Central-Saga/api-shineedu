<?php

namespace App\Modules\Learning\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Learning\Application\Services\AssignmentService;
use App\Modules\Learning\Domain\Models\Assignment;
use App\Modules\Learning\Http\Requests\StoreAssignmentRequest;
use App\Modules\Learning\Http\Requests\UpdateAssignmentRequest;
use App\Modules\Learning\Http\Resources\AssignmentResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    protected $service;

    public function __construct(AssignmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Get list of assignments with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $assignments = $this->service->list($request->all());

        return ApiResponse::paginated(
            AssignmentResource::collection($assignments),
            $assignments,
            'Assignments retrieved successfully'
        );
    }

    /**
     * Create a new assignment.
     */
    public function store(StoreAssignmentRequest $request): JsonResponse
    {
        $assignment = $this->service->create($request->validated());

        return ApiResponse::created(
            new AssignmentResource($assignment->load(['enrollment.murid', 'assignedBy'])),
            'Assignment created successfully'
        );
    }

    /**
     * Get assignment details with submissions.
     */
    public function show(int $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $assignment = $this->service->show($assignment);

        return ApiResponse::ok(
            new AssignmentResource($assignment),
            'Assignment details retrieved successfully'
        );
    }

    /**
     * Update assignment.
     */
    public function update(UpdateAssignmentRequest $request, int $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $assignment = $this->service->update($assignment, $request->validated());

        return ApiResponse::ok(
            new AssignmentResource($assignment->load(['enrollment.murid', 'assignedBy'])),
            'Assignment updated successfully'
        );
    }

    /**
     * Close assignment.
     */
    public function close(int $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $assignment = $this->service->close($assignment);

        return ApiResponse::ok(
            new AssignmentResource($assignment),
            'Assignment closed successfully'
        );
    }
}
