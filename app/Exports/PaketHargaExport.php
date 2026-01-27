<?php

namespace App\Exports;

use App\Modules\Catalog\Domain\Models\PaketHarga;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaketHargaExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = PaketHarga::query()->with(['program', 'jenjang', 'paket']);

        if ($programId = $this->request->get('program_id')) {
            if ($programId !== '__all__') {
                $query->where('program_id', $programId);
            }
        }

        if ($jenjangId = $this->request->get('jenjang_id')) {
            if ($jenjangId !== '__all__') {
                $query->where('jenjang_id', $jenjangId);
            }
        }

        if ($paketId = $this->request->get('paket_id')) {
            if ($paketId !== '__all__') {
                $query->where('paket_id', $paketId);
            }
        }

        if ($status = $this->request->get('status')) {
            if ($status !== '__all__') {
                $query->where('status', $status);
            }
        }

        $sort = $this->request->get('sort_by', 'created_at');
        $dir = $this->request->get('sort_dir', 'desc');

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Program',
            'Jenjang',
            'Paket',
            'Siswa (Min-Max)',
            'Harga',
            'Effective From',
            'Status',
        ];
    }

    public function map($ph): array
    {
        return [
            $ph->id,
            $ph->program?->nama ?? '-',
            $ph->jenjang?->nama ?? '-',
            $ph->paket?->nama ?? '-',
            $ph->min_siswa . ' - ' . $ph->max_siswa,
            number_format($ph->harga, 0, ',', '.'),
            $ph->effective_from ? $ph->effective_from->format('Y-m-d') : '-',
            $ph->status,
        ];
    }
}
