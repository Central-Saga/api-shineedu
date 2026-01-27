<?php

namespace App\Modules\AcademicSessions\Http\Controllers;

use App\Modules\AcademicSessions\Application\Services\LogbookService;
use App\Modules\AcademicSessions\Domain\Models\SesiLogbook;
use App\Modules\AcademicSessions\Domain\Models\SesiLogbookMurid;
use App\Modules\AcademicSessions\Http\Resources\LogbookMuridResource;
use App\Modules\AcademicSessions\Http\Resources\LogbookResource;
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
            $logbook ? new LogbookResource($logbook) : null,
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
            ->whereHas('enrollment', function ($q) use ($id) {
                $q->whereHas('absensi', function ($q2) use ($id) {
                    $q2->where('realisasi_jadwal_kerja_id', $id)
                        ->where('status', '!=', 'BATAL');
                });
            })
            ->with('enrollment.murid')
            ->get();

        return ApiResponse::ok(
            LogbookMuridResource::collection($logbooks),
            'Logbook murid berhasil diambil'
        );
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

    public function indexByStudent($muridId): JsonResponse
    {
        $logbooks = SesiLogbookMurid::whereHas('enrollment', function ($q) use ($muridId) {
            $q->where('murid_id', $muridId);
        })
            ->whereExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('sesi_absensi_murid')
                    ->whereColumn('sesi_absensi_murid.enrollment_id', 'sesi_logbook_murid.enrollment_id')
                    ->whereColumn('sesi_absensi_murid.realisasi_jadwal_kerja_id', 'sesi_logbook_murid.realisasi_jadwal_kerja_id')
                    ->where('sesi_absensi_murid.status', '!=', 'BATAL')
                    ->whereNull('sesi_absensi_murid.deleted_at');
            })
            ->with(['session', 'session.kelas'])
            ->latest()
            ->get();

        return ApiResponse::ok(
            LogbookMuridResource::collection($logbooks),
            'Daftar logbook murid berhasil diambil'
        );
    }

    public function indexByKelas($kelasId): JsonResponse
    {
        $logbooks = SesiLogbook::whereHas('session', function ($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })
            ->with('session')
            ->latest()
            ->get();

        return ApiResponse::ok(
            LogbookResource::collection($logbooks),
            'Daftar logbook sesi kelas berhasil diambil'
        );
    }
}
