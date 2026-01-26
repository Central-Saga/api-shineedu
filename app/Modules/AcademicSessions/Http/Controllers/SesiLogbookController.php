<?php

namespace App\Modules\AcademicSessions\Http\Controllers;

use App\Modules\AcademicSessions\Application\Services\LogbookService;
use App\Modules\AcademicSessions\Domain\Models\SesiLogbook;
use App\Modules\AcademicSessions\Domain\Models\SesiLogbookMurid;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SesiLogbookController
{
    public function __construct(
        protected LogbookService $logbookService
    ) {}

    public function show($id): JsonResponse
    {
        $logbook = SesiLogbook::where('realisasi_jadwal_kerja_id', $id)->first();

        return ApiResponse::ok(
            $logbook,
            'Logbook sesi berhasil diambil'
        );
    }

    public function upsert(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'ringkasan' => 'nullable|string',
            'materi' => 'nullable|string',
            'homework' => 'nullable|string',
            'catatan_pengajar' => 'nullable|string'
        ]);

        $logbook = $this->logbookService->upsertSessionLogbook($id, $data, auth()->id());

        return ApiResponse::ok($logbook, 'Logbook sesi berhasil disimpan');
    }

    public function showStudent($id): JsonResponse
    {
        $logbooks = SesiLogbookMurid::where('realisasi_jadwal_kerja_id', $id)
            ->with('enrollment.student')
            ->get();

        return ApiResponse::ok($logbooks, 'Logbook murid berhasil diambil');
    }

    public function bulkUpsertStudent(Request $request, $id): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.enrollment_id' => 'required|exists:enrollments,id',
            'items.*.catatan_perkembangan' => 'nullable|string',
            'items.*.kesulitan' => 'nullable|string',
            'items.*.target_next' => 'nullable|string',
            'items.*.tugas_individu' => 'nullable|string',
            'items.*.nilai_opsional' => 'nullable|numeric'
        ]);

        $this->logbookService->bulkUpsertStudentLogbook($id, $request->input('items'), auth()->id());

        return ApiResponse::ok(null, 'Logbook murid berhasil disimpan');
    }
}
