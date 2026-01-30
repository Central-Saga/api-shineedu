<?php

namespace App\Modules\Landing\Http\Controllers\Api\V2;

use App\Modules\Landing\Domain\Models\LandingGalleryItem;
use App\Modules\Landing\Http\Requests\StoreLandingGalleryItemRequest;
use App\Modules\Landing\Http\Requests\UpdateLandingGalleryItemRequest;
use App\Modules\Landing\Http\Resources\LandingGalleryItemResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingGalleryController
{
    public function index(Request $request): JsonResponse
    {
        $query = LandingGalleryItem::query()->ordered();

        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->input('is_active'));
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $items = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            LandingGalleryItemResource::collection($items),
            $items,
            'Data gallery berhasil diambil'
        );
    }

    public function store(StoreLandingGalleryItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $file = $request->file('image');

        $path = $file->store('landing-gallery', 'public');
        $data['image_path'] = $path;
        $data['sort_order'] = $data['sort_order'] ?? null;
        $data['is_active'] = $data['is_active'] ?? true;

        $item = LandingGalleryItem::create($data);

        return ApiResponse::created(
            new LandingGalleryItemResource($item),
            'Item gallery berhasil ditambahkan'
        );
    }

    public function show(LandingGalleryItem $landingGalleryItem): JsonResponse
    {
        return ApiResponse::ok(
            new LandingGalleryItemResource($landingGalleryItem),
            'Detail gallery berhasil diambil'
        );
    }

    public function update(UpdateLandingGalleryItemRequest $request, LandingGalleryItem $landingGalleryItem): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($landingGalleryItem->image_path && Storage::disk('public')->exists($landingGalleryItem->image_path)) {
                Storage::disk('public')->delete($landingGalleryItem->image_path);
            }
            $data['image_path'] = $request->file('image')->store('landing-gallery', 'public');
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $landingGalleryItem->update($data);

        return ApiResponse::ok(
            new LandingGalleryItemResource($landingGalleryItem->fresh()),
            'Item gallery berhasil diperbarui'
        );
    }

    public function destroy(LandingGalleryItem $landingGalleryItem): JsonResponse
    {
        if ($landingGalleryItem->image_path && Storage::disk('public')->exists($landingGalleryItem->image_path)) {
            Storage::disk('public')->delete($landingGalleryItem->image_path);
        }
        $landingGalleryItem->delete();

        return ApiResponse::ok(null, 'Item gallery berhasil dihapus');
    }
}
