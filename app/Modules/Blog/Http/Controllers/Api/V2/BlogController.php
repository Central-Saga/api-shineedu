<?php

namespace App\Modules\Blog\Http\Controllers\Api\V2;

use App\Modules\Blog\Domain\Models\Blog;
use App\Modules\Blog\Domain\Models\BlogAsset;
use App\Modules\Blog\Http\Requests\StoreBlogAssetRequest;
use App\Modules\Blog\Http\Requests\StoreBlogRequest;
use App\Modules\Blog\Http\Requests\UpdateBlogRequest;
use App\Modules\Blog\Http\Resources\BlogResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController
{
    public function index(Request $request): JsonResponse
    {
        $query = Blog::query()->with(['author', 'assets'])->orderByDesc('created_at');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            });
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $items = $query->paginate($perPage)->withQueryString();

        return ApiResponse::paginated(
            BlogResource::collection($items),
            $items,
            'Data blog berhasil diambil'
        );
    }

    public function store(StoreBlogRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;
        $blog = Blog::create($data);

        return ApiResponse::created(
            new BlogResource($blog->load(['author', 'assets'])),
            'Blog berhasil ditambahkan'
        );
    }

    public function show(Blog $blog): JsonResponse
    {
        $blog->load(['author', 'assets']);

        return ApiResponse::ok(
            new BlogResource($blog),
            'Detail blog berhasil diambil'
        );
    }

    public function update(UpdateBlogRequest $request, Blog $blog): JsonResponse
    {
        $blog->update($request->validated());

        return ApiResponse::ok(
            new BlogResource($blog->fresh(['author', 'assets'])),
            'Blog berhasil diperbarui'
        );
    }

    public function destroy(Blog $blog): JsonResponse
    {
        foreach ($blog->assets as $asset) {
            if (Storage::disk('public')->exists($asset->file_path)) {
                Storage::disk('public')->delete($asset->file_path);
            }
        }
        $blog->delete();

        return ApiResponse::ok(null, 'Blog berhasil dihapus');
    }

    public function storeAsset(StoreBlogAssetRequest $request, Blog $blog): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store("blog-assets/{$blog->id}", 'public');

        $asset = $blog->assets()->create([
            'file_path' => $path,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'sort_order' => $blog->assets()->max('sort_order') + 1,
        ]);

        return ApiResponse::created(
            new \App\Modules\Blog\Http\Resources\BlogAssetResource($asset),
            'Asset berhasil diunggah'
        );
    }

    public function destroyAsset(Blog $blog, BlogAsset $blogAsset): JsonResponse
    {
        if ($blogAsset->blog_id !== $blog->id) {
            return ApiResponse::notFound('Asset tidak ditemukan');
        }
        if (Storage::disk('public')->exists($blogAsset->file_path)) {
            Storage::disk('public')->delete($blogAsset->file_path);
        }
        $blogAsset->delete();

        return ApiResponse::ok(null, 'Asset berhasil dihapus');
    }
}
