<?php

namespace App\Modules\Assessment\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Modules\Assessment\Application\Services\AssessmentService;
use App\Modules\Assessment\Domain\Models\CertificateTemplate;
use App\Modules\Assessment\Http\Requests\StoreCertificateTemplateRequest;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class CertificateTemplateController extends Controller
{
    // Keeping it simple with DB facade or Eloquent as requested "Controllers must stay thin"
    // No dedicated Service needed for simple CRUD of templates if logic is minimal.

    public function index()
    {
        $templates = CertificateTemplate::latest()->get();
        return ApiResponse::ok($templates);
    }

    public function store(StoreCertificateTemplateRequest $request)
    {
        $data = $request->validated();

        $template = DB::transaction(function () use ($data) {
            $t = CertificateTemplate::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'data_mapping' => $data['data_mapping'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (isset($data['cover_image'])) {
                $t->addMedia($data['cover_image'])->toMediaCollection('cover_image');
            }
            if (isset($data['result_image'])) {
                $t->addMedia($data['result_image'])->toMediaCollection('result_image');
            }
            return $t;
        });

        return ApiResponse::created($template, 'Template berhasil dibuat');
    }

    public function show(CertificateTemplate $template)
    {
        return ApiResponse::ok($template);
    }

    public function destroy(CertificateTemplate $template)
    {
        $template->delete();
        return ApiResponse::ok(null, 'Template deleted');
    }
}
