<?php

namespace App\Modules\Catalog\Application\Services;

use App\Modules\Catalog\Domain\Models\PaketHarga;
use Illuminate\Support\Carbon;

class PricingService
{
    /**
     * Validate if a new price rule overlaps with existing active rules.
     *
     * @param array $data Input data (program_id, jenjang_id, paket_id, min_siswa, max_siswa, effective_from, effective_to)
     * @param int|null $excludeId ID to exclude from check (for update scenarios)
     * @return bool True if overlap exists, False otherwise
     */
    public function checkOverlap(array $data, ?int $excludeId = null): bool
    {
        $query = PaketHarga::query()
            ->where('program_id', $data['program_id'])
            ->where('jenjang_id', $data['jenjang_id'])
            ->where('paket_id', $data['paket_id'])
            ->where('status', 'Aktif');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Check date overlap first
        // Two date ranges A and B overlap if StartA <= EndB AND EndA >= StartB
        // Null effective_from means -infinity, Null effective_to means +infinity
        $newFrom = $data['effective_from'] ?? null;
        $newTo = $data['effective_to'] ?? null;

        // Check date overlap logic
        // Two date ranges (StartA, EndA) and (StartB, EndB) overlap if:
        // (StartA <= EndB OR EndB is NULL) AND (EndA >= StartB OR EndA is NULL)
        $query->where(function ($q) use ($newFrom, $newTo) {
            // New End check
            $q->where(function ($c1) use ($newTo) {
                if ($newTo) {
                    $c1->where('effective_from', '<=', $newTo)
                        ->orWhereNull('effective_from');
                }
            });

            // New Start check
            $q->where(function ($c2) use ($newFrom) {
                if ($newFrom) {
                    $c2->where('effective_to', '>=', $newFrom)
                        ->orWhereNull('effective_to');
                }
            });
        });

        // Check student count overlap
        // Overlap if min_siswa <= new_max AND max_siswa >= new_min
        $newMin = $data['min_siswa'];
        $newMax = $data['max_siswa'];

        $query->where(function ($q) use ($newMin, $newMax) {
            $q->where('min_siswa', '<=', $newMax ?: 999999)
                ->where(function ($sub) use ($newMin) {
                    $sub->where('max_siswa', '>=', $newMin)
                        ->orWhereNull('max_siswa');
                });
        });

        return $query->exists();
    }

    /**
     * Lookup active price based on context.
     */
    public function lookupPrice(int $programId, int $jenjangId, int $paketId, int $studentCount, ?string $date = null): ?PaketHarga
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();

        return PaketHarga::query()
            ->where('program_id', $programId)
            ->where('jenjang_id', $jenjangId)
            ->where('paket_id', $paketId)
            ->where('status', 'Aktif')
            // Student count check
            ->where('min_siswa', '<=', $studentCount)
            ->where(function ($q) use ($studentCount) {
                $q->where('max_siswa', '>=', $studentCount)
                    ->orWhereNull('max_siswa');
            })
            // Date check
            ->where(function ($q) use ($targetDate) {
                $q->where(function ($sub) use ($targetDate) {
                    $sub->where('effective_from', '<=', $targetDate)
                        ->orWhereNull('effective_from');
                })
                    ->where(function ($sub) use ($targetDate) {
                        $sub->where('effective_to', '>=', $targetDate)
                            ->orWhereNull('effective_to');
                    });
            })
            // Order by most specific (newest effective_from preferred)
            ->orderByDesc('effective_from')
            ->orderByDesc('created_at')
            ->first();
    }
}
