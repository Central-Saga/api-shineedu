<?php

namespace App\Modules\Catalog\Http\Controllers\Api\V2;

use App\Modules\Catalog\Domain\Models\Program;
use App\Modules\Catalog\Http\Requests\StoreProgramRequest;
use App\Modules\Catalog\Http\Requests\UpdateProgramRequest;
use App\Modules\Catalog\Http\Resources\ProgramResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProgramExport;

class ProgramController
{
    /**
     * Upload gambar katalog program. Mengembalikan path untuk disimpan di field program.image.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'image.required' => 'File gambar wajib diunggah.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format file tidak didukung. Harap gunakan JPG, JPEG, PNG, GIF, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
            'image.uploaded' => 'File gagal diunggah ke server (mungkin melebihi batas ukuran maksimal atau format tidak valid).',
        ]);

        $file = $request->file('image');
        $path = $file->store('program', 'public');
        $url = Storage::disk('public')->url($path);
        if (str_starts_with($url, '/')) {
            $url = rtrim(config('app.url', ''), '/') . $url;
        }

        return ApiResponse::ok([
            'path' => $path,
            'image_url' => $url,
        ], 'Gambar program berhasil diunggah');
    }
    public function index(Request $request): JsonResponse
    {
        $query = Program::query()->with('jenjangs');

        // Filter: hanya program unggulan (untuk landing home)
        if ($request->boolean('highlight')) {
            $query->highlighted();
        }

        // Search
        if ($q = $request->input('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%")
                    ->orWhere('deskripsi', 'like', "%{$q}%");
            });
        }

        // Filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int)$request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $programs = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            ProgramResource::collection($programs),
            $programs,
            'Data program berhasil diambil'
        );
    }

    public function store(StoreProgramRequest $request): JsonResponse
    {
        $data = $request->validated();
        $program = Program::create($data);

        if (isset($data['jenjang_ids'])) {
            $program->jenjangs()->sync($data['jenjang_ids']);
        }

        return ApiResponse::created(
            new ProgramResource($program->load('jenjangs')),
            'Program berhasil ditambahkan'
        );
    }

    public function show(Program $program): JsonResponse
    {
        return ApiResponse::ok(
            new ProgramResource($program->load('jenjangs')),
            'Detail program berhasil diambil'
        );
    }

    public function update(UpdateProgramRequest $request, Program $program): JsonResponse
    {
        $data = $request->validated();
        $program->update($data);

        if (isset($data['jenjang_ids'])) {
            $program->jenjangs()->sync($data['jenjang_ids']);
        }

        return ApiResponse::ok(
            new ProgramResource($program->load('jenjangs')),
            'Data program berhasil diperbarui'
        );
    }

    public function destroy(Program $program): JsonResponse
    {
        $program->delete();

        return ApiResponse::ok(null, 'Program berhasil dihapus');
    }

    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'program_' . date('Ymd_His');

        if ($format === 'sql') {
            return $this->exportSql($request, $filename);
        }

        if ($format === 'txt') {
            return $this->exportTxt($request, $filename);
        }

        if ($format === 'pdf') {
            $exporter = new ProgramExport($request);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.program', compact('items'))
                ->setPaper('a4', 'portrait');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
                'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
                'csv' => \Maatwebsite\Excel\Excel::CSV,
                'tsv' => \Maatwebsite\Excel\Excel::TSV,
                default => \Maatwebsite\Excel\Excel::XLSX,
            };

        return Excel::download(new ProgramExport($request), $filename . '.' . ($format === 'tsv' ? 'tsv' : $format), $ext);
    }

    protected function exportTxt(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new ProgramExport($request);
            $handle = fopen('php://output', 'w');
            fwrite($handle, implode("\t", $exporter->headings()) . "\n");
            $exporter->query()->chunk(100, function ($items) use ($handle, $exporter) {
                    /** @var Program $item */
                    foreach ($items as $item) {
                        fwrite($handle, implode("\t", $exporter->map($item)) . "\n");
                    }
                }
                );
                fclose($handle);
            }, $filename . '.txt', ['Content-Type' => 'text/plain']);
    }

    protected function exportSql(Request $request, string $filename)
    {
        return response()->streamDownload(function () use ($request) {
            $exporter = new ProgramExport($request);
            $query = $exporter->query();
            $handle = fopen('php://output', 'w');
            fwrite($handle, "-- Program Data Export\n\n");
            $query->chunk(100, function ($items) use ($handle) {
                    /** @var Program $item */
                    foreach ($items as $item) {
                        $sql = sprintf(
                            "INSERT INTO program (id, kode, nama, deskripsi, status, created_at, updated_at) VALUES (%d, '%s', '%s', '%s', '%s', %s, %s) ON DUPLICATE KEY UPDATE kode=VALUES(kode), nama=VALUES(nama), deskripsi=VALUES(deskripsi), status=VALUES(status), updated_at=VALUES(updated_at);\n",
                            $item->id,
                            addslashes($item->kode),
                            addslashes($item->nama),
                            addslashes((string)$item->deskripsi),
                            $item->status,
                            $item->created_at ? "'" . $item->created_at->format('Y-m-d H:i:s') . "'" : "NULL",
                            $item->updated_at ? "'" . $item->updated_at->format('Y-m-d H:i:s') . "'" : "NULL"
                        );
                        fwrite($handle, $sql);
                    }
                }
                );
                fclose($handle);
            }, $filename . '.sql', ['Content-Type' => 'application/sql']);
    }
}
