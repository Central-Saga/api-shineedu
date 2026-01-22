<?php

namespace App\Exports;

use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RolesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Role::query()->with('permissions');

        if ($keyword = $this->request->get('q')) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $sort = $this->request->get('sort_by', 'name');
        $dir = $this->request->get('sort_dir', 'asc');

        if (!in_array($sort, ['name', 'created_at', 'updated_at'])) {
            $sort = 'name';
        }

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'Role Name',
            'Permissions',
            'Created At',
        ];
    }

    public function map($role): array
    {
        return [
            $role->name,
            $role->permissions->pluck('name')->join(', '),
            $role->created_at ? $role->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
