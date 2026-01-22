<?php

namespace App\Exports;

use App\Modules\HR\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Employee::query()->with('user');

        if ($keyword = $this->request->get('q')) {
            $query->where('kode_karyawan', 'like', "%{$keyword}%")
                ->orWhereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
        }

        if ($status = $this->request->get('status')) {
            if ($status !== '__all__') {
                $query->where('status', $status);
            }
        }

        if ($kategori = $this->request->get('kategori_karyawan')) {
            if ($kategori !== '__all__') {
                $query->where('kategori_karyawan', $kategori);
            }
        }

        if ($tipe = $this->request->get('tipe_gaji')) {
            if ($tipe !== '__all__') {
                $query->where('tipe_gaji', $tipe);
            }
        }

        $sort = $this->request->get('sort_by', 'created_at');
        $dir = $this->request->get('sort_dir', 'desc');

        // Allow sorting aliases if needed, but basic validation:
        if (!in_array($sort, ['kode_karyawan', 'status', 'kategori_karyawan', 'tipe_gaji', 'gaji_pokok', 'created_at', 'updated_at'])) {
            $sort = 'created_at';
        }

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'Kode Karyawan',
            'Nama User',
            'Kategori',
            'Subtipe Kontrak',
            'Tipe Gaji',
            'Gaji Pokok',
            'Status',
            'Created At',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->kode_karyawan,
            $employee->user ? $employee->user->name : '-',
            $employee->kategori_karyawan,
            $employee->subtipe_kontrak,
            $employee->tipe_gaji,
            $employee->gaji_pokok,
            $employee->status,
            $employee->created_at ? $employee->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
