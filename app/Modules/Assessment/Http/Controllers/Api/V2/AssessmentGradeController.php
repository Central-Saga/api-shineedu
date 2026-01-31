<?php

namespace App\Modules\Assessment\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Modules\Assessment\Application\Services\AssessmentCalculationService;
use App\Modules\Assessment\Application\Services\CertificateRenderService;
use App\Modules\Assessment\Domain\Models\AssessmentGrade;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Modules\Assessment\Http\Requests\StoreAssessmentGradeRequest;

class AssessmentGradeController extends Controller
{
    public function __construct(
        protected AssessmentCalculationService $calculationService,
        protected CertificateRenderService $renderService
    ) {}

    /**
     * Store or Update Assessment Grade
     */
    public function store(StoreAssessmentGradeRequest $request)
    {
        // Validation handled by FormRequest
        $validated = $request->validated();

        // Fetch Template Type for Calculation
        $template = \App\Modules\Assessment\Domain\Models\CertificateTemplate::findOrFail($validated['certificate_template_id']);

        // Calculate (Server-Side Authority)
        $total = $this->calculationService->calculateTotal($validated['scores']);
        $average = $this->calculationService->calculateAverage($validated['scores']);
        $predicate = $this->calculationService->determinePredicate($template->type->value, $average);

        $level = null;
        if ($template->type->value === 'english') {
            $level = $this->calculationService->determineEnglishLevel($average);
        }

        $grade = DB::transaction(function () use ($validated, $total, $average, $predicate, $level) {
            return AssessmentGrade::updateOrCreate(
                [
                    'enrollment_id' => $validated['enrollment_id'],
                    'certificate_template_id' => $validated['certificate_template_id'],
                ],
                [
                    'teacher_karyawan_id' => $validated['teacher_karyawan_id'] ?? null,
                    'scores' => $validated['scores'],
                    'total_score' => $total,
                    'average_score' => $average,
                    'predicate' => $predicate,
                    'certificate_level' => $level,
                ]
            );
        });

        return ApiResponse::created($grade, 'Nilai berhasil disimpan');
    }

    public function show(AssessmentGrade $grade)
    {
        $grade->load(['enrollment.student', 'certificateTemplate', 'teacher']);
        return ApiResponse::ok($grade);
    }

    public function generate(AssessmentGrade $grade)
    {
        try {
            // Snapshot the data at this moment
            // Requirement: Ensure snapshot includes student + teacher + template identifiers
            $payload = [
                'student' => [
                    'id' => $grade->enrollment->student->id ?? null,
                    'name' => $grade->enrollment->student->nama_lengkap ?? 'Unknown',
                    'nis' => $grade->enrollment->student->nis ?? null,
                ],
                'teacher' => [
                    'id' => $grade->teacher->id ?? null,
                    'name' => $grade->teacher->user->name ?? 'Unknown',
                    'emp_id' => $grade->teacher->kode_karyawan ?? null, // nomor_induk changed to kode_karyawan based on Employee model
                ],
                'template' => [
                    'id' => $grade->certificateTemplate->id,
                    'name' => $grade->certificateTemplate->name,
                    'type' => $grade->certificateTemplate->type->value,
                ],
                'program' => $grade->enrollment->program->nama ?? 'Unknown',
                'scores' => $grade->scores,
                'final_grade' => [
                    'total' => $grade->total_score,
                    'average' => $grade->average_score,
                    'predicate' => $grade->predicate,
                    'level' => $grade->certificate_level,
                ],
                'generated_at' => now()->toIso8601String(),
            ];

            // Generate Certificate Number if missing
            if (empty($grade->certificate_no)) {
                $year = date('Y');
                $code = strtoupper(substr($grade->certificateTemplate->type->value ?? 'GEN', 0, 3));
                // Format: CERT/YEAR/TYPE/ID (Simple robust format)
                $grade->certificate_no = sprintf("CERT/%s/%s/%05d", $year, $code, $grade->id);
            }

            $grade->update([
                'payload_snapshot' => $payload,
                'certificate_no' => $grade->certificate_no
            ]);

            $url = $this->renderService->renderAndSave($grade);

            $grade->update(['generated_at' => now()]);

            return ApiResponse::ok([
                'url' => $url,
                'certificate_no' => $grade->certificate_no,
                'generated_at' => $grade->generated_at
            ], 'Sertifikat berhasil di-generate');
        } catch (\Throwable $e) {
            Log::error('Certificate Generation Failed: ' . $e->getMessage());
            return ApiResponse::serverError('Gagal generate sertifikat: ' . $e->getMessage());
        }
    }
}
