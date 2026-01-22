<?php

namespace App\Exports;

use App\Modules\Identity\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = User::query()->with('roles');

        if ($keyword = $this->request->get('q')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($status = $this->request->get('status')) {
            if ($status !== '__all__') {
                $query->where('status', $status);
            }
        }

        if ($role = $this->request->get('role')) {
            if ($role !== '__all__') {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            }
        }

        $sort = $this->request->get('sort_by', 'created_at');
        $dir = $this->request->get('sort_dir', 'desc');

        // Ensure sort column exists or fallback
        if (!in_array($sort, ['name', 'email', 'status', 'created_at', 'updated_at'])) {
            $sort = 'created_at';
        }

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Status',
            'Role',
            'Created At',
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->status,
            $user->roles->pluck('name')->join(', '),
            $user->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
