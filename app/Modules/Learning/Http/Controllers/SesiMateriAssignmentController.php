<?php

namespace App\Modules\Learning\Http\Controllers;

use App\Modules\Scheduling\Domain\Models\RealisasiJadwalKerja;
use App\Modules\Learning\Domain\Models\SesiMuridMateri;
use App\Modules\Learning\Domain\Models\SesiMuridAssignment;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SesiMateriAssignmentController
{
    /**
     * Get materi & assignments for a session
     */
    public function index(int $sesiId): JsonResponse
    {
        $sesi = RealisasiJadwalKerja::with([
            'jadwalKerja.kelas.enrollments.murid',
        ])->findOrFail($sesiId);

        // Get assigned materi
        $materiAssignments = SesiMuridMateri::where('realisasi_jadwal_kerja_id', $sesiId)
            ->with(['materi', 'enrollment.murid'])
            ->get()
            ->groupBy('materi_modul_id')
            ->map(function ($group) {
                $materi = $group->first()->materi;
                return [
                    'materi_id' => $materi->id,
                    'materi_title' => $materi->title,
                    'assigned_to' => $group->map(fn($item) => [
                        'enrollment_id' => $item->enrollment_id,
                        'murid_nama' => $item->enrollment->murid->nama_lengkap ?? 'Unknown',
                        'accessed_at' => $item->accessed_at,
                    ])->values(),
                    'total_assigned' => $group->count(),
                    'total_accessed' => $group->where('accessed_at', '!=', null)->count(),
                ];
            })->values();

        // Get assigned assignments
        $assignmentAssignments = SesiMuridAssignment::where('realisasi_jadwal_kerja_id', $sesiId)
            ->with(['assignment', 'enrollment.murid'])
            ->get()
            ->groupBy('assignment_id')
            ->map(function ($group) {
                $assignment = $group->first()->assignment;
                return [
                    'assignment_id' => $assignment->id,
                    'assignment_title' => $assignment->title,
                    'assigned_to' => $group->map(fn($item) => [
                        'enrollment_id' => $item->enrollment_id,
                        'murid_nama' => $item->enrollment->murid->nama_lengkap ?? 'Unknown',
                    ])->values(),
                    'total_assigned' => $group->count(),
                ];
            })->values();

        return ApiResponse::ok([
            'materi' => $materiAssignments,
            'assignments' => $assignmentAssignments,
        ]);
    }

    /**
     * Assign materi to students
     */
    public function assignMateri(Request $request, int $sesiId): JsonResponse
    {
        $validated = $request->validate([
            'materi_modul_id' => 'required|exists:materi_modul,id',
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'required|exists:kelas_enrollment,id',
        ]);

        DB::beginTransaction();
        try {
            $assigned = [];
            foreach ($validated['enrollment_ids'] as $enrollmentId) {
                $assignment = SesiMuridMateri::updateOrCreate(
                    [
                        'realisasi_jadwal_kerja_id' => $sesiId,
                        'enrollment_id' => $enrollmentId,
                        'materi_modul_id' => $validated['materi_modul_id'],
                    ],
                    [
                        'assigned_at' => now(),
                    ]
                );
                $assigned[] = $assignment;
            }

            DB::commit();

            return ApiResponse::created(
                ['assigned' => count($assigned)],
                'Materi berhasil di-assign'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal assign materi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Unassign materi from students
     */
    public function unassignMateri(Request $request, int $sesiId, int $materiId): JsonResponse
    {
        $validated = $request->validate([
            'enrollment_ids' => 'sometimes|array',
            'enrollment_ids.*' => 'required|exists:kelas_enrollment,id',
        ]);

        $query = SesiMuridMateri::where('realisasi_jadwal_kerja_id', $sesiId)
            ->where('materi_modul_id', $materiId);

        if (isset($validated['enrollment_ids'])) {
            $query->whereIn('enrollment_id', $validated['enrollment_ids']);
        }

        $deleted = $query->delete();

        return ApiResponse::ok(
            ['deleted' => $deleted],
            'Materi berhasil di-unassign'
        );
    }

    /**
     * Assign assignment to students
     */
    public function assignAssignment(Request $request, int $sesiId): JsonResponse
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:assignment,id',
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'required|exists:kelas_enrollment,id',
        ]);

        DB::beginTransaction();
        try {
            $assigned = [];
            foreach ($validated['enrollment_ids'] as $enrollmentId) {
                $assignment = SesiMuridAssignment::updateOrCreate(
                    [
                        'realisasi_jadwal_kerja_id' => $sesiId,
                        'enrollment_id' => $enrollmentId,
                        'assignment_id' => $validated['assignment_id'],
                    ],
                    [
                        'assigned_at' => now(),
                    ]
                );
                $assigned[] = $assignment;
            }

            DB::commit();

            return ApiResponse::created(
                ['assigned' => count($assigned)],
                'Tugas berhasil di-assign'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal assign tugas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Unassign assignment from students
     */
    public function unassignAssignment(Request $request, int $sesiId, int $assignmentId): JsonResponse
    {
        $validated = $request->validate([
            'enrollment_ids' => 'sometimes|array',
            'enrollment_ids.*' => 'required|exists:kelas_enrollment,id',
        ]);

        $query = SesiMuridAssignment::where('realisasi_jadwal_kerja_id', $sesiId)
            ->where('assignment_id', $assignmentId);

        if (isset($validated['enrollment_ids'])) {
            $query->whereIn('enrollment_id', $validated['enrollment_ids']);
        }

        $deleted = $query->delete();

        return ApiResponse::ok(
            ['deleted' => $deleted],
            'Tugas berhasil di-unassign'
        );
    }
}
