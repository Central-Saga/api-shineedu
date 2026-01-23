<?php

namespace App\Exports;

use App\Modules\HR\Domain\Models\Cuti;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CutiExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Cuti::query()->with(['karyawan.user', 'approver']);

        if ($keyword = $this->request->get('q')) {
            $query->whereHas('karyawan.user', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            })->orWhereHas('karyawan', function ($q) use ($keyword) {
                $q->where('kode_karyawan', 'like', "%{$keyword}%");
            });
        }

        if ($status = $this->request->get('status')) {
            if ($status !== '__all__') {
                $query->where('status', $status);
            }
        }

        if ($jenis = $this->request->get('jenis')) {
            if ($jenis !== '__all__') {
                $query->where('jenis', $jenis);
            }
        }

        if ($startDate = $this->request->get('start_date')) {
            $query->where('start_date', '>=', $startDate);
        }

        if ($endDate = $this->request->get('end_date')) {
            $query->where('end_date', '<=', $endDate);
        }

        $sort = $this->request->get('sort_by', 'created_at');
        $dir = $this->request->get('sort_dir', 'desc');

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Karyawan',
            'Nama Karyawan',
            'Jenis',
            'Status',
            'Start Date',
            'End Date',
            'Keterangan',
            'Disetujui Oleh',
            'Potongan Tipe',
            'Potongan Nilai',
            'Created At',
        ];
    }

    public function map($cuti): array
    {
        return [
            $cuti->id,
            $cuti->karyawan?->kode_karyawan ?? '-',
            $cuti->karyawan?->user?->name ?? '-',
            $cuti->jenis,
            $cuti->status,
            $cuti->start_date,
            $cuti->end_date,
            $cuti->keterangan,
            $cuti->approver?->name ?? '-',
            $cuti->potongan_tipe,
            $cuti->potongan_nilai,
            $cuti->created_at ? $cuti->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
