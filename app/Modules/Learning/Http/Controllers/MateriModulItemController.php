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
            'type' => ['sometimes', 'string', 'in:VIDEO,PDF,LINK,TEXT,QUIZ,FILE'],
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'file_path' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:102400'],
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
}
