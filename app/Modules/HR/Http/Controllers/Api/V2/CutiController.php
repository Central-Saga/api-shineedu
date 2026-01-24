<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Application\Services\CutiService;
use App\Modules\HR\Domain\Models\Cuti;
use App\Modules\HR\Http\Requests\StoreCutiRequest;
use App\Modules\HR\Http\Requests\UpdateCutiRequest;
use App\Modules\HR\Http\Resources\CutiResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Exports\CutiExport;
use Maatwebsite\Excel\Facades\Excel;

class CutiController
{
    public function __construct(
        protected CutiService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getList($request->all());

        return ApiResponse::paginated(
            CutiResource::collection($data),
            $data,
            'Data cuti berhasil diambil'
        );
    }

    public function store(StoreCutiRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            if (empty($data['keterangan']) && !empty($data['catatan'])) {
                $data['keterangan'] = $data['catatan'];
            }

            $cuti = $this->service->createCuti($data);

            return ApiResponse::created(
                new CutiResource($cuti->load('karyawan.user')),
                'Pengajuan cuti berhasil dibuat'
            );
        } catch (ValidationException $e) {
            return ApiResponse::validation($e->errors(), 'Validasi Gagal');
        }
    }

    public function show(Cuti $cuti): JsonResponse
    {
        $cuti->load(['karyawan.user', 'approver']);

        return ApiResponse::ok(
            new CutiResource($cuti),
            'Detail cuti berhasil diambil'
        );
    }

    public function update(UpdateCutiRequest $request, Cuti $cuti): JsonResponse
    {
        $updated = $this->service->update($cuti, $request->validated());

        return ApiResponse::ok(
            new CutiResource($updated->load(['karyawan.user', 'approver'])),
            'Data cuti berhasil diperbarui'
        );
    }

    public function destroy(Cuti $cuti): JsonResponse
    {
        $this->service->delete($cuti);

        return ApiResponse::ok(null, 'Data cuti berhasil dihapus');
    }

    public function export(Request $request)
    {
        $format = $request->get('export', 'xlsx');
        $filename = 'cuti_' . date('Ymd_His');

        if ($format === 'pdf') {
            $exporter = new CutiExport($request);
            $items = $exporter->query()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.cuti', compact('items'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        $ext = match ($format) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            'txt' => \Maatwebsite\Excel\Excel::TSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new CutiExport($request), $filename . '.' . $format, $ext);
    }

    public function approve(Cuti $cuti): JsonResponse
    {
        $updated = $this->service->approve($cuti, auth()->id());

        return ApiResponse::ok(
            new CutiResource($updated),
            'Pengajuan cuti berhasil disetujui'
        );
    }

    public function reject(Cuti $cuti): JsonResponse
    {
        $updated = $this->service->reject($cuti, auth()->id());

        return ApiResponse::ok(
            new CutiResource($updated),
            'Pengajuan cuti ditolak'
        );
    }

    public function cancel(Cuti $cuti): JsonResponse
    {
        $user = auth()->user();
        $isOwner = $cuti->karyawan->user_id === $user->id;
        $canManage = $user->can('cuti.manage');

        if (! $canManage && ! $isOwner) {
            return ApiResponse::forbidden('Anda tidak memiliki akses untuk membatalkan pengajuan ini');
        }

        // Owner can cancel 'diajukan' or 'disetujui'
        if ($isOwner && ! $canManage && ! in_array($cuti->status, ['diajukan', 'disetujui'])) {
            return ApiResponse::forbidden('Hanya pengajuan berstatus diajukan atau disetujui yang dapat dibatalkan');
        }

        $updated = $this->service->cancel($cuti, $user->id);

        return ApiResponse::ok(
            new CutiResource($updated),
            'Pengajuan cuti berhasil dibatalkan'
        );
    }
}
