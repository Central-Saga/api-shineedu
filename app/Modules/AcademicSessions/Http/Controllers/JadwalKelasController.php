<?php

namespace App\Modules\AcademicSessions\Http\Controllers;

use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use App\Modules\Scheduling\Http\Resources\JadwalKerjaResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JadwalKelasController
{
    public function index($kelasId): JsonResponse
    {
        $jadwal = JadwalKerja::where('kelas_id', $kelasId)
            ->with(['guru.user'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return ApiResponse::ok(
            JadwalKerjaResource::collection($jadwal), // Using existing resource
            'Jadwal kelas berhasil diambil'
        );
    }

    public function store(Request $request, $kelasId): JsonResponse
    {
        // Reuse StoreJadwalKerjaRequest validation? Or custom validation.
        // Since we are creating for specific kelas_id, we need to inject it.

        $validated = $request->validate([
            'hari' => 'required|string', // Senin, Selasa...
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'guru_pengajar_id' => 'required|exists:users,id', // Or employees
            'ruangan_kelas' => 'nullable|string',
            'kategori' => 'required|string', // PRIVATE/REGULER matching class type
            'mata_pelajaran' => 'nullable|string',
            'tarif' => 'nullable|numeric',
            'status' => 'string|in:Aktif,Non Aktif'
        ]);

        $validated['kelas_id'] = $kelasId;
        // Defaulting status to Aktif if not set
        $validated['status'] = $validated['status'] ?? 'Aktif';

        $jadwal = JadwalKerja::create($validated);

        return ApiResponse::created(
            new JadwalKerjaResource($jadwal),
            'Jadwal berhasil ditambahkan ke kelas'
        );
    }
}
