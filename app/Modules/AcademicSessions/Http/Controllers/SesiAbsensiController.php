<?php

namespace App\Modules\AcademicSessions\Http\Controllers;

use App\Modules\AcademicSessions\Application\Services\AbsensiService;
use App\Modules\AcademicSessions\Domain\Models\SesiAbsensiMurid;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SesiAbsensiController
{
    public function __construct(
        protected AbsensiService $absensiService
    ) {}

    public function index($id): JsonResponse
    {
        $absensi = SesiAbsensiMurid::where('realisasi_jadwal_kerja_id', $id)
            ->with(['enrollment.murid', 'createdBy']) // Adjust relations if needed
            ->get();

        return ApiResponse::ok(
            $absensi, // Can wrap in Resource if needed
            'Data absensi berhasil diambil'
        );
    }

    public function bulkUpdate(Request $request, $id): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.enrollment_id' => 'required|exists:enrollments,id',
            'items.*.status' => 'required|in:HADIR,TIDAK_HADIR,PINDAH_JADWAL',
            'items.*.catatan' => 'nullable|string',
            'items.*.target_session_id' => 'required_if:items.*.status,PINDAH_JADWAL|exists:realisasi_jadwal_kerja,id'
        ]);

        $this->absensiService->bulkUpdate($id, $request->input('items'), auth()->id());

        return ApiResponse::ok(null, 'Absensi berhasil diperbarui');
    }

    public function moveAttendance(Request $request, $id): JsonResponse
    {
        $request->validate([
            'enrollment_id' => 'required|exists:kelas_enrollment,enrollment_id',
            'target_session_id' => 'required|exists:realisasi_jadwal_kerja,id'
        ]);

        $this->absensiService->moveAttendance(
            $id,
            $request->input('enrollment_id'),
            $request->input('target_session_id'),
            auth()->id()
        );

        return ApiResponse::ok(null, 'Murid berhasil dipindahkan ke sesi lain');
    }
}
