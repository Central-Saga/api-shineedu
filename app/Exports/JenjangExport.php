<?php

namespace App\Exports;

use App\Modules\Catalog\Domain\Models\Jenjang;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JenjangExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Jenjang::query();

        if ($keyword = $this->request->get('q')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('kode', 'like', "%{$keyword}%");
            });
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
            'Status',
            'Created At',
            'Updated At',
        ];
    }

    public function map($jenjang): array
    {
        return [
            $jenjang->id,
            $jenjang->kode,
            $jenjang->nama,
            $jenjang->status,
            $jenjang->created_at ? $jenjang->created_at->format('Y-m-d H:i:s') : '-',
            $jenjang->updated_at ? $jenjang->updated_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
