<?php

namespace App\Modules\Learning\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Learning\Application\Services\MateriModulService;
use App\Modules\Learning\Domain\Models\MateriModul;
use App\Modules\Learning\Domain\Models\MateriModulItem;
use App\Modules\Learning\Http\Requests\StoreMateriModulItemRequest;
use App\Modules\Learning\Http\Resources\MateriModulItemResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MateriModulItemController extends Controller
{
    protected $service;

    public function __construct(MateriModulService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all items for a materi modul.
     */
    public function index(int $id): JsonResponse
    {
        $modul = MateriModul::findOrFail($id);
        $items = $modul->items()->orderBy('order_no')->get();

        return ApiResponse::ok(
            MateriModulItemResource::collection($items),
            'Items retrieved successfully'
        );
    }

    /**
     * Add item to materi modul.
     */
    public function store(StoreMateriModulItemRequest $request, int $id): JsonResponse
    {
        $modul = MateriModul::findOrFail($id);

        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('materi/items', 'public');
        }

        $item = $this->service->addItem($modul, $data);

        return ApiResponse::created(
            new MateriModulItemResource($item),
            'Item added successfully'
        );
    }

    /**
     * Update item.
     */
    public function update(Request $request, int $itemId): JsonResponse
    {
        $item = MateriModulItem::findOrFail($itemId);

        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'in:FILE,URL'],
            'title' => ['sometimes', 'string', 'max:255'],
            'url' => ['nullable', 'url'],
            'file' => [
                'nullable',
                'file',
                'max:51200', // 50MB max
                'mimes:doc,docx,xls,xlsx,pdf,ppt,pptx,jpg,jpeg,png,gif,zip'
            ],
            'order_no' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('materi/items', 'public');
        }

        $item = $this->service->updateItem($item, $validated);

        return ApiResponse::ok(
            new MateriModulItemResource($item),
            'Item updated successfully'
        );
    }

    /**
     * Delete item.
     */
    public function destroy(int $itemId): JsonResponse
    {
        $item = MateriModulItem::findOrFail($itemId);
        $this->service->deleteItem($item);

        return ApiResponse::ok(null, 'Item deleted successfully');
    }

    /**
     * Reorder items within a modul.
     */
    public function reorder(Request $request, int $id): JsonResponse
    {
        $modul = MateriModul::findOrFail($id);

        $validated = $request->validate([
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['integer', 'exists:materi_modul_item,id'],
        ]);

        $this->service->reorderItems($modul, $validated['item_ids']);

        return ApiResponse::ok(null, 'Items reordered successfully');
    }

    /**
     * Toggle item status (active/inactive).
     */
    public function toggleStatus(int $itemId): JsonResponse
    {
        $item = MateriModulItem::findOrFail($itemId);

        $item->is_active = !$item->is_active;
        $item->save();

        return ApiResponse::ok(
            new MateriModulItemResource($item),
            'Status updated successfully'
        );
    }
}
