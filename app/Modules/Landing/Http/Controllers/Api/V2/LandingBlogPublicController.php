<?php

namespace App\Modules\Landing\Http\Controllers\Api\V2;

use App\Modules\Landing\Domain\Models\BlogPost;
use App\Modules\Landing\Http\Resources\BlogPostResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingBlogPublicController
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()
            ->published()
            ->with(['author', 'assets'])
            ->ordered();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $items = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            BlogPostResource::collection($items),
            $items,
            'OK'
        );
    }

    public function show(int $id): JsonResponse
    {
        $post = BlogPost::query()
            ->published()
            ->with(['author', 'assets'])
            ->find($id);

        if (!$post) {
            return ApiResponse::notFound('Blog tidak ditemukan');
        }

        return ApiResponse::ok(
            new BlogPostResource($post),
            'OK'
        );
    }
}
