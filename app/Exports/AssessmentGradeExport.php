<?php

namespace App\Exports;

use App\Modules\Assessment\Domain\Models\AssessmentGrade;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssessmentGradeExport implements FromQuery, WithHeadings, WithMapping
{
    // We'll collect all unique score keys first to build dynamic columns
    protected $scoreKeys = [];

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
        // Pre-fetch keys from the dataset to build headers
        // Note: This adds a query, but it ensures we know all possible keys
        // Alternatively, we could just hardcode common keys if they are fixed.
        // For dynamic, we might scan the first 100 records or similar.
        // For now, let's try to get them from the data we are about to export.

        // Optimization: We could just assume standard keys if they are consistent.
        // If they vary wildly per record, CSV structure might be inconsistent.
        // Let's stick effectively to key-value string for safety OR
        // if user wants separate columns, we must know them beforehand.

        // Since user asked "bisa gak saat export nilainya dipisah?",
        // let's try to separate them.

        $this->detectScoreKeys();
    }

    protected function detectScoreKeys()
    {
        // Get all unique keys from the JSON scores column for the filtered dataset
        // This might be heavy on large datasets.
        // A safer approach might be to just pick distinct keys from the first few records
        // or fetch all distinct JSON keys if DB supports it (PGSQL does, MySQL 5.7+ partial).

        // Let's grab the first 50 records to sample keys
        $samples = $this->query()->limit(50)->get();
        $keys = [];
        foreach ($samples as $sample) {
            $scores = $sample->scores;
            if (is_string($scores)) {
                $scores = json_decode($scores, true);
            }
            if (is_array($scores)) {
                foreach (array_keys($scores) as $k) {
                    $keys[$k] = true;
                }
            }
        }
        $this->scoreKeys = array_keys($keys);
    }

    public function query()
    {
        $query = AssessmentGrade::query()
            ->with(['enrollment.student', 'enrollment.program', 'certificateTemplate', 'teacher.user']);

        if ($this->request->has('q') && !empty($this->request->q)) {
            $q = $this->request->q;
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('enrollment.student', function ($sq) use ($q) {
                    $sq->where('nama_lengkap', 'like', "%$q%");
                })->orWhere('certificate_no', 'like', "%$q%");
            });
        }

        if ($this->request->has('type') && !empty($this->request->type)) {
            $query->whereHas('certificateTemplate', function ($sq) {
                $sq->where('type', $this->request->type);
            });
        }

        if ($this->request->has('generated_status') && !empty($this->request->generated_status)) {
            if ($this->request->generated_status === 'generated') {
                $query->whereNotNull('generated_at');
            } else {
                $query->whereNull('generated_at');
            }
        }

        $sort = $this->request->get('sort_by', 'created_at');
        $dir = $this->request->get('sort_dir', 'desc');

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        $baseHeadings = [
            'ID',
            'No Sertifikat',
            'Nama Murid',
            'NIS Murid',
            'Program',
            'Guru',
            'Template',
            'Total Score',
            'Average Score',
            'Predicate',
            'Certificate Level',
        ];

        // Add dynamic score headers
        foreach ($this->scoreKeys as $key) {
            $baseHeadings[] = 'Nilai: ' . ucwords(str_replace('_', ' ', $key));
        }

        return array_merge($baseHeadings, [
            'Generated At',
            'Created At',
        ]);
    }

    public function map($grade): array
    {
        $row = [
            $grade->id,
            $grade->certificate_no,
            $grade->enrollment->student->nama_lengkap ?? '-',
            $grade->enrollment->student->nis ?? '-',
            $grade->enrollment->program->nama ?? '-',
            $grade->teacher->user->name ?? '-',
            $grade->certificateTemplate->name ?? '-',
            $grade->total_score,
            $grade->average_score,
            $grade->predicate,
            $grade->certificate_level,
        ];

        // Map dynamic scores
        $scores = $grade->scores;
        if (is_string($scores)) {
            $scores = json_decode($scores, true) ?? [];
        }

        foreach ($this->scoreKeys as $key) {
            $row[] = $scores[$key] ?? '';
        }

        return array_merge($row, [
            $grade->generated_at ? $grade->generated_at->format('Y-m-d H:i:s') : '-',
            $grade->created_at ? $grade->created_at->format('Y-m-d H:i:s') : '-',
        ]);
    }
}
