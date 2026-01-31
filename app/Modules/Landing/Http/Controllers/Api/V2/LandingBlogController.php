<?php

namespace App\Modules\Landing\Http\Controllers\Api\V2;

use App\Modules\Landing\Domain\Models\BlogPost;
use App\Modules\Landing\Http\Requests\StoreBlogPostRequest;
use App\Modules\Landing\Http\Requests\UpdateBlogPostRequest;
use App\Modules\Landing\Http\Resources\BlogPostResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingBlogController
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()->with(['author', 'assets'])->ordered();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            });
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $items = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            BlogPostResource::collection($items),
            $items,
            'Data blog berhasil diambil'
        );
    }

    public function store(StoreBlogPostRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;

        if ($request->hasFile('image')) {
            $data['featured_image_path'] = $request->file('image')->store('landing-blog', 'public');
        }

        $post = BlogPost::create($data);
        $post->load(['author', 'assets']);

        return ApiResponse::created(
            new BlogPostResource($post),
            'Blog berhasil ditambahkan'
        );
    }

    public function show(BlogPost $blogPost): JsonResponse
    {
        $blogPost->load(['author', 'assets']);

        return ApiResponse::ok(
            new BlogPostResource($blogPost),
            'Detail blog berhasil diambil'
        );
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($blogPost->featured_image_path && Storage::disk('public')->exists($blogPost->featured_image_path)) {
                Storage::disk('public')->delete($blogPost->featured_image_path);
            }
            $data['featured_image_path'] = $request->file('image')->store('landing-blog', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($blogPost->featured_image_path && Storage::disk('public')->exists($blogPost->featured_image_path)) {
                Storage::disk('public')->delete($blogPost->featured_image_path);
            }
            $data['featured_image_path'] = null;
        }

        $blogPost->update($data);
        $blogPost->load(['author', 'assets']);

        return ApiResponse::ok(
            new BlogPostResource($blogPost->fresh()),
            'Blog berhasil diperbarui'
        );
    }

    public function destroy(BlogPost $blogPost): JsonResponse
    {
        if ($blogPost->featured_image_path && Storage::disk('public')->exists($blogPost->featured_image_path)) {
            Storage::disk('public')->delete($blogPost->featured_image_path);
        }
        $blogPost->delete();

        return ApiResponse::ok(null, 'Blog berhasil dihapus');
    }
}
