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
            'items.*.status' => 'required|in:HADIR,IZIN,SAKIT,ALPHA,BATAL',
            'items.*.catatan' => 'nullable|string'
        ]);

        $this->absensiService->bulkUpdate($id, $request->input('items'), auth()->id());

        return ApiResponse::ok(null, 'Absensi berhasil diperbarui');
    }
}
