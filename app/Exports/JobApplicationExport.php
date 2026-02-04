<?php

namespace App\Exports;

use App\Modules\HR\Domain\Models\JobApplication;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JobApplicationExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = JobApplication::query()->with(['jobVacancy']);

        if ($this->request->has('q') && !empty($this->request->q)) {
            $q = $this->request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('first_name', 'like', "%$q%")
                    ->orWhere('last_name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('tracking_code', 'like', "%$q%");
            });
        }

        if ($this->request->has('status') && !empty($this->request->status)) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->has('job_vacancy_id') && !empty($this->request->job_vacancy_id)) {
            $query->where('job_vacancy_id', $this->request->job_vacancy_id);
        }

        $sort = $this->request->get('sort_by', 'created_at');
        $dir = $this->request->get('sort_dir', 'desc');

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tracking Code',
            'Full Name',
            'Email',
            'Phone',
            'Position',
            'Education',
            'Experience',
            'Status',
            'Apply Date',
        ];
    }

    public function map($application): array
    {
        return [
            $application->id,
            $application->tracking_code,
            $application->first_name . ' ' . $application->last_name,
            $application->email,
            $application->phone,
            $application->jobVacancy->title ?? '-',
            $application->education,
            $application->experience,
            $application->status,
            $application->created_at ? $application->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
