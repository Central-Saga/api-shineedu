<?php

namespace App\Exports;

use App\Modules\Catalog\Domain\Models\Program;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProgramExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Program::query()->with('jenjangs');

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
            'Deskripsi',
            'Status',
            'Created At',
            'Updated At',
        ];
    }

    public function map($program): array
    {
        return [
            $program->id,
            $program->kode,
            $program->nama,
            $program->deskripsi,
            $program->status,
            $program->created_at ? $program->created_at->format('Y-m-d H:i:s') : '-',
            $program->updated_at ? $program->updated_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
