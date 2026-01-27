<?php

namespace App\Exports;

use App\Modules\Catalog\Domain\Models\Paket;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaketExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Paket::query();

        if ($keyword = $this->request->get('q')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('kode', 'like', "%{$keyword}%");
            });
        }

        if ($tipe = $this->request->get('tipe')) {
            if ($tipe !== '__all__') {
                $query->where('tipe', $tipe);
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
            'Kode',
            'Nama',
            'Tipe',
            'Pertemuan/Bulan',
            'Durasi (Menit)',
            'Status',
            'Created At',
            'Updated At',
        ];
    }

    public function map($paket): array
    {
        return [
            $paket->id,
            $paket->kode,
            $paket->nama,
            $paket->tipe,
            $paket->pertemuan_per_bulan,
            $paket->durasi_menit,
            $paket->status,
            $paket->created_at ? $paket->created_at->format('Y-m-d H:i:s') : '-',
            $paket->updated_at ? $paket->updated_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
