<?php

namespace App\Modules\AcademicSessions\Application\Services;

use App\Modules\AcademicSessions\Domain\Models\SesiLogbook;
use App\Modules\AcademicSessions\Domain\Models\SesiLogbookMurid;
use App\Modules\AcademicSessions\Domain\Models\Session;
use Illuminate\Support\Facades\DB;

class LogbookService
{
    public function upsertSessionLogbook($sessionId, array $data, $userId)
    {
        $session = Session::findOrFail($sessionId);

        $logbook = SesiLogbook::updateOrCreate(
            ['realisasi_jadwal_kerja_id' => $sessionId],
            array_merge($data, ['created_by' => $userId])
        );

        return $logbook;
    }

    public function bulkUpsertStudentLogbook($sessionId, array $items, $userId)
    {
        $session = Session::findOrFail($sessionId);

        DB::transaction(function () use ($sessionId, $items, $userId) {
            foreach ($items as $item) {
                SesiLogbookMurid::updateOrCreate(
                    [
                        'realisasi_jadwal_kerja_id' => $sessionId,
                        'enrollment_id' => $item['enrollment_id']
                    ],
                    [
                        'catatan_perkembangan' => $item['catatan_perkembangan'] ?? null,
                        'kesulitan' => $item['kesulitan'] ?? null,
                        'target_next' => $item['target_next'] ?? null,
                        'tugas_individu' => $item['tugas_individu'] ?? null,
                        'nilai_opsional' => $item['nilai_opsional'] ?? null,
                        'created_by' => $userId
                    ]
                );
            }
        });

        return true;
    }
}
