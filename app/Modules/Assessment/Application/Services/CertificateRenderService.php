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

        $coverMedia = $template->getFirstMedia('cover_image');
        $resultMedia = $template->getFirstMedia('result_image');

        $coverBase64 = null;
        if ($coverMedia && file_exists($coverMedia->getPath())) {
            $type = pathinfo($coverMedia->getPath(), PATHINFO_EXTENSION);
            $data = file_get_contents($coverMedia->getPath());
            $coverBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $resultBase64 = null;
        if ($resultMedia && file_exists($resultMedia->getPath())) {
            $type = pathinfo($resultMedia->getPath(), PATHINFO_EXTENSION);
            $data = file_get_contents($resultMedia->getPath());
            $resultBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // Use mapping from DB or fallback
        $mapping = $template->data_mapping ?? [];

        // 2. Render HTML
        $html = view('assessment::certificate.pdf_render', [
            'grade' => $grade,
            'enrollment' => $grade->enrollment,
            'student' => $grade->enrollment->student,
            'template' => $template,
            'coverUrl' => $coverBase64,
            'resultUrl' => $resultBase64,
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
            ->landscape()
            ->format('A4')
            ->save($tempPath);

        // 5. Move to Media Library
        $grade->clearMediaCollection('certificate_pdf');

        $media = $grade->addMedia($tempPath)
            ->toMediaCollection('certificate_pdf');

        return $media->getUrl();
    }
}
