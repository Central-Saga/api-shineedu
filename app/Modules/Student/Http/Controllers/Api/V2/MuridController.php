<?php

namespace App\Modules\Student\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Modules\Student\Application\Services\MuridService;
use App\Modules\Student\Domain\Models\Murid;
use App\Modules\Student\Http\Requests\StoreMuridRequest;
use App\Modules\Student\Http\Requests\UpdateMuridRequest;
use App\Modules\Student\Http\Resources\MuridResource;
use Illuminate\Http\Request;

class MuridController extends Controller
{
    protected $muridService;

    public function __construct(MuridService $muridService)
    {
        $this->muridService = $muridService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->muridService->getList($request->all());

        return MuridResource::collection($data)->additional([
            'success' => true,
            'message' => 'Data murid berhasil diambil',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMuridRequest $request)
    {
        $murid = $this->muridService->create($request->validated());

        return (new MuridResource($murid))->additional([
            'success' => true,
            'message' => 'Murid berhasil ditambahkan',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Murid $murid)
    {
        $murid = $this->muridService->show($murid);

        return (new MuridResource($murid))->additional([
            'success' => true,
            'message' => 'Detail murid berhasil diambil',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMuridRequest $request, Murid $murid)
    {
        $murid = $this->muridService->update($murid, $request->validated());

        return (new MuridResource($murid))->additional([
            'success' => true,
            'message' => 'Data murid berhasil diperbarui',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Murid $murid)
    {
        $this->muridService->delete($murid);

        return response()->json([
            'success' => true,
            'message' => 'Murid berhasil dihapus',
            'data' => null,
        ]);
    }
}
