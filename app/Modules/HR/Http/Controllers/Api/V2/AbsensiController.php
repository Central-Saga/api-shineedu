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
use App\Exports\AbsensiExport;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController
{
    private const ALLOWED_LOCATIONS = [
        [
            'lat' => -8.5207986,
            'lng' => 115.137969,
        ],
        [
            'lat' => -8.438817,
            'lng' => 115.123787,
        ],
    ];
    private const MAX_RADIUS_METERS = 50;

    public function __construct(
        protected AbsensiService $service
    ) {}

    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getList($request->all());

        return ApiResponse::paginated(
            AbsensiResource::collection($data),
            $data,
            'Data absensi berhasil diambil'
        );
    }

    public function todayStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return ApiResponse::fail('User is not linked to an employee record', 400);
        }

        /** @var Absensi|null $absensi */
        $absensi = Absensi::where('karyawan_id', $employee->id)
            ->where('tanggal', now()->format('Y-m-d'))
            ->first();

        return ApiResponse::ok(
            $absensi ? new AbsensiResource($absensi) : null,
            'Status absensi berhasil diambil'
        );
    }

    public function store(StoreAbsensiRequest $request): JsonResponse
    {
        $absensi = $this->service->create($request->validated());

        return ApiResponse::created(
            new AbsensiResource($absensi->load(['karyawan.user', 'media'])),
            'Absensi berhasil dicatat'
        );
    }

    public function show(Absensi $absensi): JsonResponse
    {
        $absensi->load(['karyawan.user', 'media']);

        return ApiResponse::ok(
            new AbsensiResource($absensi),
            'Detail absensi berhasil diambil'
        );
    }

    public function update(UpdateAbsensiRequest $request, Absensi $absensi): JsonResponse
    {
        $updated = $this->service->update($absensi, $request->validated());

        return ApiResponse::ok(
            new AbsensiResource($updated->load(['karyawan.user', 'media'])),
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

        $minDistance = INF;
        foreach (self::ALLOWED_LOCATIONS as $loc) {
            $dist = $this->calculateDistance(
                $loc['lat'],
                $loc['lng'],
                $request->latitude,
                $request->longitude
            );
            if ($dist < $minDistance) {
                $minDistance = $dist;
            }
        }

        if ($minDistance > self::MAX_RADIUS_METERS) {
            return ApiResponse::fail(sprintf('Anda berada di luar radius penjemputan/absen (%.2fm). Maksimal radius adalah %dm.', $minDistance, self::MAX_RADIUS_METERS), 400);
        }

        $user = $request->user();
        $employee = $user->employee; // Assuming relationship exists User -> Employee

        if (!$employee) {
            return ApiResponse::fail('User is not linked to an employee record', 400);
        }

        // Check if already checked in today
        /** @var Absensi|null $existing */
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
            $absensi->addMediaFromRequest('photo')
                ->toMediaCollection('attendance_photos');
        }

        return ApiResponse::created(
            new AbsensiResource($absensi->load('media')),
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

        $minDistance = INF;
        foreach (self::ALLOWED_LOCATIONS as $loc) {
            $dist = $this->calculateDistance(
                $loc['lat'],
                $loc['lng'],
                $request->latitude,
                $request->longitude
            );
            if ($dist < $minDistance) {
                $minDistance = $dist;
            }
        }

        if ($minDistance > self::MAX_RADIUS_METERS) {
            return ApiResponse::fail(sprintf('Anda berada di luar radius penjemputan/absen (%.2fm). Maksimal radius adalah %dm.', $minDistance, self::MAX_RADIUS_METERS), 400);
        }

        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return ApiResponse::fail('User is not linked to an employee record', 400);
        }

        /** @var Absensi|null $absensi */
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
            $absensi->addMediaFromRequest('photo')
                ->toMediaCollection('attendance_photos_out');
        }

        return ApiResponse::ok(
            new AbsensiResource($absensi->load('media')),
            'Check-out berhasil'
        );
    }

    public function destroy(Absensi $absensi): JsonResponse
    {
        $this->service->delete($absensi);

        return ApiResponse::ok(null, 'Data absensi berhasil dihapus');
    }

    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'absensi_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new AbsensiExport($request);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.absensi', compact('items'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new AbsensiExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    /**
     * Helper to export TXT.
     */
    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new AbsensiExport($request);
            $handle = fopen('php://output', 'w');

            // Headings
            fwrite($handle, implode("\t", $exporter->headings()) . "\n");

            $exporter->query()->chunk(100, function ($items) use ($handle, $exporter) {
                foreach ($items as $item) {
                    fwrite($handle, implode("\t", $exporter->map($item)) . "\n");
                }
            });

            fclose($handle);
        }, $filename . '.txt', [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Helper to export SQL.
     */
    protected function exportSql(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new AbsensiExport($request);
            $query = $exporter->query();

            $handle = fopen('php://output', 'w');

            fwrite($handle, "-- Shine Education Bali - Absensi Data Export\n");
            fwrite($handle, "-- Generated at " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $query->chunk(100, function ($absensis) use ($handle) {
                /** @var Absensi $absensi */
                foreach ($absensis as $absensi) {
                    $vals = [
                        $absensi->id,
                        $absensi->karyawan_id,
                        addslashes((string)$absensi->tanggal),
                        $absensi->jam_masuk ? "'" . addslashes((string)$absensi->jam_masuk) . "'" : 'NULL',
                        $absensi->jam_pulang ? "'" . addslashes((string)$absensi->jam_pulang) . "'" : 'NULL',
                        addslashes((string)$absensi->status_kehadiran),
                        addslashes((string)$absensi->sumber_absen),
                        $absensi->durasi ?? 'NULL',
                        $absensi->latitude ?? 'NULL',
                        $absensi->longitude ?? 'NULL',
                        $absensi->created_at ? "'" . addslashes((string)$absensi->created_at) . "'" : 'NULL',
                        $absensi->updated_at ? "'" . addslashes((string)$absensi->updated_at) . "'" : 'NULL',
                    ];
                    $sql = sprintf(
                        "INSERT INTO absensi (id, karyawan_id, tanggal, jam_masuk, jam_pulang, status_kehadiran, sumber_absen, durasi, latitude, longitude, created_at, updated_at) VALUES (%d, %d, '%s', %s, %s, '%s', '%s', %s, %s, %s, %s, %s);\n",
                        ...$vals
                    );
                    fwrite($handle, $sql);
                }
            });

            fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);
        }, $filename . '.sql', [
            'Content-Type' => 'application/sql',
        ]);
    }
}
