<?php

namespace App\Modules\Scheduling\Application\Services;

use App\Modules\Scheduling\Domain\Models\JadwalKerja;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class JadwalKerjaBulkService
{
    protected array $rules = [
        'kategori' => 'required|string|max:255',
        'mata_pelajaran' => 'nullable|string|max:255',
        'hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
        'nomor_sesi' => 'nullable|string|max:50',
        'jam_mulai' => 'required|date_format:H:i',
        'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        'tarif' => 'required|numeric|min:0',
        'status' => 'required|string|in:Aktif,Non Aktif',
        'ruangan_kelas' => 'nullable|string|max:255',
        'guru_pengajar_id' => 'required|exists:karyawan,id',
        'kelas_id' => 'nullable|exists:kelas,id',
    ];

    public function bulkCreate(array $items, bool $dryRun = false): array
    {
        $results = [];
        $created = 0;
        $failed = 0;
        $valid = 0;

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                $failed++;
                $results[] = [
                    'index' => $index,
                    'status' => 'failed',
                    'errors' => ['item' => ['Item harus berupa object.']],
                ];
                continue;
            }

            $validator = Validator::make($item, $this->rules);
            if ($validator->fails()) {
                $failed++;
                $results[] = [
                    'index' => $index,
                    'status' => 'failed',
                    'errors' => $validator->errors()->toArray(),
                ];
                continue;
            }

            $duplicateQuery = JadwalKerja::where('guru_pengajar_id', $item['guru_pengajar_id'])
                ->where('hari', $item['hari'])
                ->where('jam_mulai', $item['jam_mulai'])
                ->where('jam_selesai', $item['jam_selesai']);

            $kelasId = $item['kelas_id'] ?? null;
            if ($kelasId === null || $kelasId === '') {
                $duplicateQuery->whereNull('kelas_id');
            } else {
                $duplicateQuery->where('kelas_id', $kelasId);
            }

            if ($duplicateQuery->exists()) {
                $failed++;
                $results[] = [
                    'index' => $index,
                    'status' => 'failed',
                    'errors' => ['duplicate' => ['Jadwal sudah ada.']],
                ];
                continue;
            }

            if ($dryRun) {
                $valid++;
                $results[] = [
                    'index' => $index,
                    'status' => 'valid',
                ];
                continue;
            }

            try {
                $payload = Arr::only($item, [
                    'kategori',
                    'mata_pelajaran',
                    'hari',
                    'nomor_sesi',
                    'jam_mulai',
                    'jam_selesai',
                    'tarif',
                    'status',
                    'ruangan_kelas',
                    'guru_pengajar_id',
                    'kelas_id',
                ]);

                foreach (['mata_pelajaran', 'nomor_sesi', 'ruangan_kelas', 'kelas_id'] as $key) {
                    if (array_key_exists($key, $payload) && $payload[$key] === '') {
                        $payload[$key] = null;
                    }
                }

                $jadwal = JadwalKerja::create($payload);
                $created++;
                $results[] = [
                    'index' => $index,
                    'status' => 'created',
                    'id' => $jadwal->id,
                ];
            } catch (\Throwable $e) {
                $failed++;
                $results[] = [
                    'index' => $index,
                    'status' => 'failed',
                    'errors' => ['exception' => [$e->getMessage()]],
                ];
            }
        }

        return [
            'total' => count($items),
            'created' => $created,
            'failed' => $failed,
            'valid' => $valid,
            'dry_run' => $dryRun,
            'results' => $results,
        ];
    }
}
