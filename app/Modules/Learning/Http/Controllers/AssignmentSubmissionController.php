<?php

namespace App\Modules\Learning\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Learning\Application\Services\AssignmentService;
use App\Modules\Learning\Domain\Models\Assignment;
use App\Modules\Learning\Domain\Models\AssignmentSubmission;
use App\Modules\Learning\Http\Requests\ReviewSubmissionRequest;
use App\Modules\Learning\Http\Requests\StoreSubmissionRequest;
use App\Modules\Learning\Http\Resources\AssignmentSubmissionResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class AssignmentSubmissionController extends Controller
{
    protected $service;

    public function __construct(AssignmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Get submissions for an assignment.
     */
    public function index(int $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $submissions = $this->service->getSubmissions($assignment);

        return ApiResponse::ok(
            AssignmentSubmissionResource::collection($submissions),
            'Submissions retrieved successfully'
        );
    }

    /**
     * Submit assignment (by student).
     */
    public function store(StoreSubmissionRequest $request, int $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);

        // Check if assignment can accept submissions
        if (!$assignment->canSubmit()) {
            return ApiResponse::badRequest('Assignment sudah ditutup atau tidak bisa menerima submission.');
        }

        $submission = $this->service->submit($assignment, $request->validated());

        return ApiResponse::created(
            new AssignmentSubmissionResource($submission),
            'Submission created successfully'
        );
    }

    /**
     * Review a submission (by tutor).
     */
    public function review(ReviewSubmissionRequest $request, int $submissionId): JsonResponse
    {
        $submission = AssignmentSubmission::findOrFail($submissionId);

        $submission = $this->service->reviewSubmission($submission, $request->validated());

        return ApiResponse::ok(
            new AssignmentSubmissionResource($submission),
            'Submission reviewed successfully'
        );
    }
}
