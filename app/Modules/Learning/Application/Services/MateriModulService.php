<?php

namespace App\Modules\Learning\Application\Services;

use App\Modules\Learning\Domain\Models\MateriModul;
use App\Modules\Learning\Domain\Models\MateriModulItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MateriModulService
{
    /**
     * Get list of materi modul with filters.
     */
    public function list(array $params): LengthAwarePaginator
    {
        $query = MateriModul::query()
            ->with(['program', 'jenjang', 'creator', 'items']);

        // Search by title or description
        if (!empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Filters
        if (!empty($params['program_id'] ?? null)) {
            $query->where('program_id', $params['program_id']);
        }
        if (!empty($params['jenjang_id'] ?? null)) {
            $query->where('jenjang_id', $params['jenjang_id']);
        }
        if (isset($params['is_active'])) {
            $query->where('is_active', filter_var($params['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDir = $params['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($params['per_page'] ?? 15);
    }

    /**
     * Create a new materi modul.
     */
    public function create(array $data): MateriModul
    {
        return DB::transaction(function () use ($data) {
            $modul = MateriModul::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'program_id' => $data['program_id'] ?? null,
                'jenjang_id' => $data['jenjang_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // Create items if provided
            if (!empty($data['items'])) {
                foreach ($data['items'] as $index => $itemData) {
                    $modul->items()->create([
                        'type' => $itemData['type'],
                        'title' => $itemData['title'],
                        'content' => $itemData['content'] ?? null,
                        'url' => $itemData['url'] ?? null,
                        'file_path' => $itemData['file_path'] ?? null,
                        'order_no' => $itemData['order_no'] ?? ($index + 1),
                        'is_active' => $itemData['is_active'] ?? true,
                    ]);
                }
            }

            return $modul->load(['items', 'program', 'jenjang', 'creator']);
        });
    }

    /**
     * Show materi modul details.
     */
    public function show(MateriModul $modul): MateriModul
    {
        return $modul->load(['items', 'program', 'jenjang', 'creator']);
    }

    /**
     * Update materi modul.
     */
    public function update(MateriModul $modul, array $data): MateriModul
    {
        $modul->update([
            'title' => $data['title'] ?? $modul->title,
            'description' => $data['description'] ?? $modul->description,
            'program_id' => $data['program_id'] ?? $modul->program_id,
            'jenjang_id' => $data['jenjang_id'] ?? $modul->jenjang_id,
            'is_active' => $data['is_active'] ?? $modul->is_active,
        ]);

        return $modul->load(['items', 'program', 'jenjang', 'creator']);
    }

    /**
     * Delete materi modul (soft delete).
     */
    public function delete(MateriModul $modul): void
    {
        $modul->delete();
    }

    /**
     * Add item to materi modul.
     */
    public function addItem(MateriModul $modul, array $data): MateriModulItem
    {
        $maxOrder = $modul->items()->max('order_no') ?? 0;

        return $modul->items()->create([
            'type' => (string) $data['type'],
            'title' => (string) $data['title'],
            'content' => $data['content'] ?? null,
            'url' => $data['url'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'order_no' => $data['order_no'] ?? ($maxOrder + 1),
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update item.
     */
    public function updateItem(MateriModulItem $item, array $data): MateriModulItem
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete item.
     */
    public function deleteItem(MateriModulItem $item): void
    {
        $item->delete();
    }

    /**
     * Reorder items within a modul.
     */
    public function reorderItems(MateriModul $modul, array $orderedIds): void
    {
        DB::transaction(function () use ($modul, $orderedIds) {
            foreach ($orderedIds as $index => $itemId) {
                $modul->items()->where('id', $itemId)->update(['order_no' => $index + 1]);
            }
        });
    }
}
