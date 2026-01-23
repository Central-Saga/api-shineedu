<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Application\Services\AbsensiService;
use App\Modules\HR\Domain\Models\Absensi;
use App\Modules\HR\Http\Requests\StoreAbsensiRequest;
use App\Modules\HR\Http\Requests\UpdateAbsensiRequest;
use App\Modules\HR\Http\Resources\AbsensiResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsensiController
{
    public function __construct(
        protected AbsensiService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getList($request->all());

        return ApiResponse::paginated(
            AbsensiResource::collection($data),
            $data,
            'Data absensi berhasil diambil'
        );
    }

    public function store(StoreAbsensiRequest $request): JsonResponse
    {
        $absensi = $this->service->create($request->validated());

        return ApiResponse::created(
            new AbsensiResource($absensi->load('karyawan.user')),
            'Absensi berhasil dicatat'
        );
    }

    public function show(Absensi $absensi): JsonResponse
    {
        $absensi->load('karyawan.user');

        return ApiResponse::ok(
            new AbsensiResource($absensi),
            'Detail absensi berhasil diambil'
        );
    }

    public function update(UpdateAbsensiRequest $request, Absensi $absensi): JsonResponse
    {
        $updated = $this->service->update($absensi, $request->validated());

        return ApiResponse::ok(
            new AbsensiResource($updated->load('karyawan.user')),
            'Data absensi berhasil diperbarui'
        );
    }

    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|max:10240', // 10MB
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'qr_data' => 'nullable|string',
        ]);

        $user = $request->user();
        $employee = $user->employee; // Assuming relationship exists User -> Employee

        if (!$employee) {
            return ApiResponse::fail('User is not linked to an employee record', 400);
        }

        // Check if already checked in today
        $existing = Absensi::where('karyawan_id', $employee->id)
            ->where('tanggal', now()->format('Y-m-d'))
            ->first();

        if ($existing) {
            return ApiResponse::fail('Anda sudah melakukan absen hari ini', 400);
        }

        $absensi = new Absensi();
        $absensi->karyawan_id = $employee->id;
        $absensi->tanggal = now()->format('Y-m-d');
        $absensi->jam_masuk = now();
        $absensi->status_kehadiran = 'hadir';
        $absensi->sumber_absen = 'web';
        $absensi->latitude = $request->latitude;
        $absensi->longitude = $request->longitude;
        $absensi->qr_code_data = $request->input('qr_data');
        $absensi->save();

        if ($request->hasFile('photo')) {
            $absensi->addMediaFromRequest('photo')->toMediaCollection('attendance_photos');
        }

        return ApiResponse::created(
            new AbsensiResource($absensi),
            'Check-in berhasil'
        );
    }

    public function checkOut(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return ApiResponse::fail('User is not linked to an employee record', 400);
        }

        $absensi = Absensi::where('karyawan_id', $employee->id)
            ->where('tanggal', now()->format('Y-m-d'))
            ->first();

        if (!$absensi) {
            return ApiResponse::fail('Anda belum melakukan check-in hari ini', 404);
        }

        if ($absensi->jam_pulang) {
            return ApiResponse::fail('Anda sudah melakukan check-out hari ini', 400);
        }

        $now = now();
        $absensi->jam_pulang = $now;

        // Ensure jam_masuk and jam_pulang are Carbon objects
        $masuk = \Carbon\Carbon::parse($absensi->jam_masuk);
        $pulang = \Carbon\Carbon::parse($now);
        $absensi->durasi = $masuk->diffInMinutes($pulang);

        $absensi->save();

        if ($request->hasFile('photo')) {
            $absensi->addMediaFromRequest('photo')->toMediaCollection('attendance_photos_out');
        }

        return ApiResponse::ok(
            new AbsensiResource($absensi),
            'Check-out berhasil'
        );
    }

    public function destroy(Absensi $absensi): JsonResponse
    {
        $this->service->delete($absensi);

        return ApiResponse::ok(null, 'Data absensi berhasil dihapus');
    }
}
