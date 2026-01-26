<?php

namespace App\Modules\Academic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Application\Services\KelasService;
use App\Modules\Academic\Http\Requests\AddKelasAnggotaRequest;
use App\Modules\Academic\Http\Resources\KelasResource;
use App\Modules\Academic\Domain\Models\Kelas;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class KelasAnggotaController extends Controller
{
    private $kelasService;

    public function __construct(KelasService $kelasService)
    {
        $this->kelasService = $kelasService;
    }

    public function index($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->load(['enrollments']);

        return ApiResponse::ok(new KelasResource($kelas), 'Daftar anggota kelas');
    }

    public function store(AddKelasAnggotaRequest $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $updatedKelas = $this->kelasService->addAnggota($kelas, $request->input('enrollment_ids'));

        return ApiResponse::ok(new KelasResource($updatedKelas), 'Anggota berhasil ditambahkan');
    }

    public function destroy($id, $enrollmentId)
    {
        $kelas = Kelas::findOrFail($id);

        $this->kelasService->removeAnggota($kelas, $enrollmentId);

        return ApiResponse::ok(null, 'Anggota berhasil dikeluarkan');
    }
}
