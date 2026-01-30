<?php

namespace App\Modules\Learning\Application\Services;

use App\Modules\Learning\Domain\Models\Assignment;
use App\Modules\Learning\Domain\Models\AssignmentSubmission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssignmentService
{
    /**
     * Get list of assignments with filters.
     */
    public function list(array $params): LengthAwarePaginator
    {
        $query = Assignment::query()
            ->with(['enrollment.murid', 'sesi', 'materiModul', 'assignedBy', 'latestSubmission']);

        // Search by title
        if (!empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where('title', 'like', "%{$keyword}%");
        }

        // Filters
        if (!empty($params['enrollment_id'] ?? null)) {
            $query->where('enrollment_id', $params['enrollment_id']);
        }
        if (!empty($params['sesi_id'] ?? null)) {
            $query->where('realisasi_jadwal_kerja_id', $params['sesi_id']);
        }
        if (!empty($params['status'] ?? null)) {
            $query->where('status', $params['status']);
        }
        if (!empty($params['materi_modul_id'] ?? null)) {
            $query->where('materi_modul_id', $params['materi_modul_id']);
        }

        // Due date range filter
        if (!empty($params['due_from'] ?? null)) {
            $query->where('due_at', '>=', $params['due_from']);
        }
        if (!empty($params['due_to'] ?? null)) {
            $query->where('due_at', '<=', $params['due_to']);
        }

        // Overdue filter
        if (!empty($params['overdue'] ?? null) && filter_var($params['overdue'], FILTER_VALIDATE_BOOLEAN)) {
            $query->overdue();
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDir = $params['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($params['per_page'] ?? 15);
    }

    /**
     * Create a new assignment.
     */
    public function create(array $data): Assignment
    {
        return Assignment::create([
            'enrollment_id' => $data['enrollment_id'],
            'realisasi_jadwal_kerja_id' => $data['realisasi_jadwal_kerja_id'] ?? null,
            'materi_modul_id' => $data['materi_modul_id'] ?? null,
            'title' => $data['title'],
            'instructions' => $data['instructions'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'status' => Assignment::STATUS_ASSIGNED,
            'assigned_by' => $data['assigned_by'] ?? auth()->id(),
        ]);
    }

    /**
     * Show assignment details with submissions.
     */
    public function show(Assignment $assignment): Assignment
    {
        return $assignment->load([
            'enrollment.murid',
            'sesi',
            'materiModul.items',
            'assignedBy',
            'submissions.submittedBy',
            'submissions.reviewer',
        ]);
    }

    /**
     * Update assignment.
     */
    public function update(Assignment $assignment, array $data): Assignment
    {
        $assignment->update([
            'title' => $data['title'] ?? $assignment->title,
            'instructions' => $data['instructions'] ?? $assignment->instructions,
            'due_at' => $data['due_at'] ?? $assignment->due_at,
            'status' => $data['status'] ?? $assignment->status,
            'materi_modul_id' => $data['materi_modul_id'] ?? $assignment->materi_modul_id,
        ]);

        return $assignment;
    }

    /**
     * Close assignment.
     */
    public function close(Assignment $assignment): Assignment
    {
        $assignment->close();
        return $assignment;
    }

    /**
     * Submit assignment (by student).
     */
    public function submit(Assignment $assignment, array $data): AssignmentSubmission
    {
        return DB::transaction(function () use ($assignment, $data) {
            // Handle file upload if present
            $attachmentPath = null;
            if (!empty($data['attachment'])) {
                $attachmentPath = $data['attachment']->store('assignments/submissions', 'public');
            }

            $submission = $assignment->submissions()->create([
                'submitted_by' => $data['submitted_by'] ?? auth()->id(),
                'submitted_at' => now(),
                'content_text' => $data['content_text'] ?? null,
                'attachment_path' => $attachmentPath ?? ($data['attachment_path'] ?? null),
                'status' => AssignmentSubmission::STATUS_SUBMITTED,
            ]);

            // Update assignment status if this is the first submission
            if ($assignment->status === Assignment::STATUS_ASSIGNED) {
                $assignment->update(['status' => Assignment::STATUS_SUBMITTED]);
            }

            return $submission->load(['submittedBy']);
        });
    }

    /**
     * Get submissions for an assignment.
     */
    public function getSubmissions(Assignment $assignment): \Illuminate\Database\Eloquent\Collection
    {
        return $assignment->submissions()
            ->with(['submittedBy', 'reviewer'])
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Review a submission (by tutor).
     */
    public function reviewSubmission(AssignmentSubmission $submission, array $data): AssignmentSubmission
    {
        return DB::transaction(function () use ($submission, $data) {
            $status = $data['status']; // ACCEPTED or REVISION_REQUESTED
            $reviewer = auth()->user();

            $submission->update([
                'status' => $status,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'feedback' => $data['feedback'] ?? null,
                'score' => $data['score'] ?? null,
            ]);

            // If accepted, update assignment status
            if ($status === AssignmentSubmission::STATUS_ACCEPTED) {
                $submission->assignment->update(['status' => Assignment::STATUS_REVIEWED]);
            }

            return $submission->load(['submittedBy', 'reviewer', 'assignment']);
        });
    }
}
