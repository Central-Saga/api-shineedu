<?php

namespace App\Modules\Assessment\Application\Services;

use App\Modules\Assessment\Domain\Models\AssessmentGrade;
use Spatie\Browsershot\Browsershot;

class CertificateRenderService
{
    /**
     * Render the certificate and save it to media library
     */
    public function renderAndSave(AssessmentGrade $grade): string
    {
        // 1. Prepare Data
        $template = $grade->certificateTemplate;
        $coverUrl = $template->getFirstMediaUrl('cover_image'); // Local path or URL
        $resultUrl = $template->getFirstMediaUrl('result_image');

        // Use mapping from DB or fallback
        $mapping = $template->data_mapping ?? [];

        // 2. Render HTML
        $html = view('assessment::certificate.pdf_render', [
            'grade' => $grade,
            'enrollment' => $grade->enrollment,
            'student' => $grade->enrollment->student,
            'template' => $template,
            'coverUrl' => $coverUrl,
            'resultUrl' => $resultUrl,
            'mapping' => $mapping,
        ])->render();

        // 3. Define Temporary Path
        // We use a timestamped filename to avoid caching issues
        $filename = "cert_{$grade->id}_" . time() . ".pdf";
        $tempPath = storage_path("app/temp/{$filename}");

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // 4. Generate PDF via Browsershot
        Browsershot::html($html)
            ->setChromePath('/usr/bin/chromium')
            ->noSandbox()
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->format('A4')
            ->save($tempPath);

        // 5. Move to Media Library
        $grade->clearMediaCollection('certificate_pdf');

        $media = $grade->addMedia($tempPath)
            ->toMediaCollection('certificate_pdf');

        return $media->getUrl();
    }
}
