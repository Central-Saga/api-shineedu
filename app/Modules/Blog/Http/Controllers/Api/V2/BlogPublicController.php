<?php

namespace App\Modules\Blog\Http\Controllers\Api\V2;

use App\Modules\Blog\Domain\Models\Blog;
use App\Modules\Blog\Http\Resources\BlogResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogPublicController
{
    public function index(Request $request): JsonResponse
    {
        $query = Blog::query()
            ->published()
            ->with(['author', 'assets'])
            ->orderByDesc('created_at');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);
        $items = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            BlogResource::collection($items),
            $items,
            'OK'
        );
    }

    public function show(Blog $blog): JsonResponse
    {
        if ($blog->status !== 'published') {
            return ApiResponse::notFound('Blog tidak ditemukan');
        }
        $blog->load(['author', 'assets']);

        return ApiResponse::ok(
            new BlogResource($blog),
            'OK'
        );
    }
}
