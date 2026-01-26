<?php

namespace App\Modules\AcademicSessions\Http\Controllers;

use App\Modules\AcademicSessions\Application\Services\GenerateSesiService;
use App\Modules\AcademicSessions\Application\Services\SesiService;
use App\Modules\AcademicSessions\Http\Resources\SessionResource;
use App\Shared\Http\Responses\ApiResponse; // Verify path or use standard
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SesiController
{
    public function __construct(
        protected SesiService $sesiService,
        protected GenerateSesiService $generateService
    ) {}

    public function indexByKelas(Request $request, $kelasId): JsonResponse
    {
        // TODO: Validate user can view this kelas session (Permission)

        $filters = [
            'start_date' => $request->get('from'),
            'end_date' => $request->get('to'),
            'status_sesi' => $request->get('status_sesi'),
            'per_page' => $request->get('per_page'),
        ];

        $sessions = $this->sesiService->getSesiByKelas($kelasId, $filters);

        return ApiResponse::paginated(
            SessionResource::collection($sessions),
            $sessions,
            'Data sesi berhasil diambil'
        );
    }

    public function show($id): JsonResponse
    {
        $session = $this->sesiService->findById($id);

        return ApiResponse::ok(
            new SessionResource($session),
            'Detail sesi berhasil diambil'
        );
    }

    public function generate(Request $request, $kelasId): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'sumber' => 'in:SYSTEM,MANUAL',
            'auto_populate_absensi' => 'boolean'
        ]);

        $result = $this->generateService->generateForKelas(
            $kelasId,
            $request->get('from'),
            $request->get('to'),
            $request->get('sumber', 'SYSTEM'),
            $request->boolean('auto_populate_absensi', true)
        );

        return ApiResponse::ok(
            ['count' => $result['count']], // Optional data return
            $result['message']
        );
    }

    public function update(Request $request, $id): JsonResponse
    {
        // Validation handled inside Service or Request. Do basic generic validation here or generic Request
        $validData = $request->validate([
            'status_sesi' => 'string|in:TERJADWAL,BERJALAN,SELESAI,BATAL,LIBUR',
            'status_kehadiran_guru' => 'string|in:HADIR,IZIN,SAKIT,ALPHA,DIGANTI',
            'jam_mulai_aktual' => 'nullable|date_format:H:i', // Or datetime depending on frontend, Service expects string H:i or handle it
            'jam_selesai_aktual' => 'nullable|date_format:H:i',
            'catatan' => 'nullable|string',
            'guru_pengganti_id' => 'nullable|exists:users,id', // or employees
            'ruangan_kelas' => 'nullable|string',
            'alasan_batal' => 'nullable|string',
            'is_hangus' => 'boolean'
        ]);

        $session = $this->sesiService->update($id, $validData, auth()->id());

        return ApiResponse::ok(
            new SessionResource($session),
            'Sesi berhasil diperbarui'
        );
    }

    public function syncAnggota($id): JsonResponse
    {
        $count = $this->sesiService->syncAnggota($id);

        return ApiResponse::ok(
            ['added_count' => $count],
            'Sinkronisasi anggota berhasil'
        );
    }
}
