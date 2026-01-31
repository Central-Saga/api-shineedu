<?php

namespace App\Modules\Assessment\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Modules\Assessment\Domain\Models\AssessmentGrade;
use Illuminate\Http\Request;

class CertificatePublicController extends Controller
{
    /**
     * Download certificate by certificate number
     */
    public function download($no)
    {
        /** @var AssessmentGrade $grade */
        $grade = AssessmentGrade::where('certificate_no', $no)->firstOrFail();

        $media = $grade->getFirstMedia('certificate_pdf');

        if (!$media) {
            abort(404, 'File sertifikat tidak ditemukan');
        }

        return response()->download($media->getPath(), $media->file_name);
    }
}
