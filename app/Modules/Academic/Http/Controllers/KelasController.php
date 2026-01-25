<?php

namespace App\Modules\Academic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Application\Services\KelasService;
use App\Modules\Academic\Http\Requests\StoreKelasRequest;
use App\Modules\Academic\Http\Requests\UpdateKelasRequest;
use App\Modules\Academic\Http\Resources\KelasResource;
use App\Modules\Academic\Domain\Models\Kelas;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    private $kelasService;

    public function __construct(KelasService $kelasService)
    {
        $this->kelasService = $kelasService;
    }

    public function index(Request $request)
    {
        $data = $this->kelasService->listKelas($request->all());
        return ApiResponse::paginated(KelasResource::collection($data), $data, 'List Kelas');
    }

    public function store(StoreKelasRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $kelas = $this->kelasService->storeKelas($data);

        return ApiResponse::created(new KelasResource($kelas), 'Kelas berhasil dibuat');
    }

    public function show($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas = $this->kelasService->showKelas($kelas);

        return ApiResponse::ok(new KelasResource($kelas), 'Detail kelas');
    }

    public function update(UpdateKelasRequest $request, $id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas = $this->kelasService->updateKelas($kelas, $request->validated());

        return ApiResponse::ok(new KelasResource($kelas), 'Kelas berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $this->kelasService->deleteKelas($kelas);

        return ApiResponse::ok(null, 'Kelas berhasil dihapus');
    }
}
