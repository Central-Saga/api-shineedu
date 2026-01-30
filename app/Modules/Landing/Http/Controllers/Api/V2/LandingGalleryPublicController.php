<?php

namespace App\Modules\Landing\Http\Controllers\Api\V2;

use App\Modules\Landing\Domain\Models\LandingGalleryItem;
use App\Modules\Landing\Http\Resources\LandingGalleryItemResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class LandingGalleryPublicController
{
    public function index(): JsonResponse
    {
        $items = LandingGalleryItem::query()
            ->active()
            ->ordered()
            ->get();

        return ApiResponse::ok(
            LandingGalleryItemResource::collection($items),
            'OK'
        );
    }
}
