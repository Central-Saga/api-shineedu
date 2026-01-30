<?php

namespace App\Modules\Learning\Http\Controllers;

use App\Modules\Learning\Domain\Models\SesiMuridMateri;
use App\Modules\Learning\Domain\Models\SesiMuridAssignment;
use App\Modules\Learning\Domain\Models\AssignmentSubmission;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentPortalController
{
    /**
     * Get materi assigned to current student
     */
    public function getMyMateri(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Get student's enrollment
        $enrollment = DB::table('kelas_enrollment')
            ->join('murid', 'kelas_enrollment.murid_id', '=', 'murid.id')
            ->where('murid.user_id', $user->id)
            ->select('kelas_enrollment.id as enrollment_id')
            ->first();

        if (!$enrollment) {
            return ApiResponse::ok([
                'materi' => [],
                'message' => 'No enrollment found'
            ]);
        }

        $materiAssignments = SesiMuridMateri::where('enrollment_id', $enrollment->enrollment_id)
            ->with([
                'materi.items',
                'sesi.kelas',
            ])
            ->orderBy('assigned_at', 'desc')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'materi_id' => $assignment->materi_id,
                    'materi_title' => $assignment->materi->title ?? 'Unknown',
                    'materi_description' => $assignment->materi->description ?? null,
                    'items_count' => $assignment->materi->items->count() ?? 0,
                    'assigned_at' => $assignment->assigned_at,
                    'accessed_at' => $assignment->accessed_at,
                    'is_accessed' => $assignment->accessed_at !== null,
                    'sesi_date' => $assignment->sesi->tanggal ?? null,
                    'kelas_name' => $assignment->sesi->kelas->nama_kelas ?? null,
                ];
            });

        return ApiResponse::ok([
            'materi' => $materiAssignments,
            'total' => $materiAssignments->count(),
            'accessed' => $materiAssignments->where('is_accessed', true)->count(),
            'pending' => $materiAssignments->where('is_accessed', false)->count(),
        ]);
    }

    /**
     * Get assignments assigned to current student
     */
    public function getMyAssignments(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Get student's enrollment
        $enrollment = DB::table('kelas_enrollment')
            ->join('murid', 'kelas_enrollment.murid_id', '=', 'murid.id')
            ->where('murid.user_id', $user->id)
            ->select('kelas_enrollment.id as enrollment_id', 'kelas_enrollment.murid_id')
            ->first();

        if (!$enrollment) {
            return ApiResponse::ok([
                'assignments' => [],
                'message' => 'No enrollment found'
            ]);
        }

        $assignmentAssignments = SesiMuridAssignment::where('enrollment_id', $enrollment->enrollment_id)
            ->with([
                'assignment',
                'sesi.kelas',
            ])
            ->orderBy('assigned_at', 'desc')
            ->get()
            ->map(function ($assignment) use ($enrollment) {
                // Check submission status
                $submission = AssignmentSubmission::where('assignment_id', $assignment->assignment_id)
                    ->where('murid_id', $enrollment->murid_id)
                    ->first();

                return [
                    'id' => $assignment->id,
                    'assignment_id' => $assignment->assignment_id,
                    'assignment_title' => $assignment->assignment->title ?? 'Unknown',
                    'assignment_description' => $assignment->assignment->description ?? null,
                    'due_date' => $assignment->assignment->due_date ?? null,
                    'assigned_at' => $assignment->assigned_at,
                    'sesi_date' => $assignment->sesi->tanggal ?? null,
                    'kelas_name' => $assignment->sesi->kelas->nama_kelas ?? null,
                    'submission' => $submission ? [
                        'id' => $submission->id,
                        'status' => $submission->status,
                        'submitted_at' => $submission->submitted_at,
                        'score' => $submission->score,
                        'feedback' => $submission->feedback,
                    ] : null,
                    'is_submitted' => $submission !== null,
                    'is_overdue' => $assignment->assignment->due_date
                        ? now()->isAfter($assignment->assignment->due_date) && !$submission
                        : false,
                ];
            });

        return ApiResponse::ok([
            'assignments' => $assignmentAssignments,
            'total' => $assignmentAssignments->count(),
            'submitted' => $assignmentAssignments->where('is_submitted', true)->count(),
            'pending' => $assignmentAssignments->where('is_submitted', false)->count(),
            'overdue' => $assignmentAssignments->where('is_overdue', true)->count(),
        ]);
    }

    /**
     * Mark materi as accessed
     */
    public function markMateriAccessed(int $materiAssignmentId): JsonResponse
    {
        $user = Auth::user();

        // Get student's enrollment
        $enrollment = DB::table('kelas_enrollment')
            ->join('murid', 'kelas_enrollment.murid_id', '=', 'murid.id')
            ->where('murid.user_id', $user->id)
            ->select('kelas_enrollment.id as enrollment_id')
            ->first();

        if (!$enrollment) {
            return ApiResponse::error('Enrollment not found', 404);
        }

        $assignment = SesiMuridMateri::where('id', $materiAssignmentId)
            ->where('enrollment_id', $enrollment->enrollment_id)
            ->first();

        if (!$assignment) {
            return ApiResponse::error('Materi assignment not found', 404);
        }

        if (!$assignment->accessed_at) {
            $assignment->accessed_at = now();
            $assignment->save();
        }

        return ApiResponse::ok([
            'accessed_at' => $assignment->accessed_at
        ], 'Materi marked as accessed');
    }

    /**
     * Get student progress analytics
     */
    public function getProgress(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Get student's enrollment
        $enrollment = DB::table('kelas_enrollment')
            ->join('murid', 'kelas_enrollment.murid_id', '=', 'murid.id')
            ->where('murid.user_id', $user->id)
            ->select('kelas_enrollment.id as enrollment_id', 'kelas_enrollment.murid_id')
            ->first();

        if (!$enrollment) {
            return ApiResponse::ok([
                'progress' => [],
                'message' => 'No enrollment found'
            ]);
        }

        // Materi stats
        $totalMateri = SesiMuridMateri::where('enrollment_id', $enrollment->enrollment_id)->count();
        $accessedMateri = SesiMuridMateri::where('enrollment_id', $enrollment->enrollment_id)
            ->whereNotNull('accessed_at')
            ->count();

        // Assignment stats
        $totalAssignments = SesiMuridAssignment::where('enrollment_id', $enrollment->enrollment_id)->count();
        $submittedAssignments = AssignmentSubmission::where('murid_id', $enrollment->murid_id)->count();

        // Score average
        $averageScore = AssignmentSubmission::where('murid_id', $enrollment->murid_id)
            ->whereNotNull('score')
            ->avg('score');

        // Recent activity (last 7 days)
        $recentMateri = SesiMuridMateri::where('enrollment_id', $enrollment->enrollment_id)
            ->where('accessed_at', '>=', now()->subDays(7))
            ->count();

        $recentSubmissions = AssignmentSubmission::where('murid_id', $enrollment->murid_id)
            ->where('submitted_at', '>=', now()->subDays(7))
            ->count();

        return ApiResponse::ok([
            'materi' => [
                'total' => $totalMateri,
                'accessed' => $accessedMateri,
                'pending' => $totalMateri - $accessedMateri,
                'completion_rate' => $totalMateri > 0 ? round(($accessedMateri / $totalMateri) * 100, 1) : 0,
            ],
            'assignments' => [
                'total' => $totalAssignments,
                'submitted' => $submittedAssignments,
                'pending' => $totalAssignments - $submittedAssignments,
                'completion_rate' => $totalAssignments > 0 ? round(($submittedAssignments / $totalAssignments) * 100, 1) : 0,
                'average_score' => $averageScore ? round($averageScore, 1) : null,
            ],
            'recent_activity' => [
                'materi_accessed_7days' => $recentMateri,
                'assignments_submitted_7days' => $recentSubmissions,
            ],
        ]);
    }
}
