<?php

namespace App\Modules\Academic\Application\Services;

use App\Modules\Academic\Domain\Models\Kelas;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class KelasService
{
    public function listKelas(array $filters)
    {
        $query = Kelas::with(['program', 'jenjang', 'creator'])->withCount('enrollments');

        if (!empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_kelas', 'like', '%' . $filters['q'] . '%')
                    ->orWhere('kode_kelas', 'like', '%' . $filters['q'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }

        if (!empty($filters['jenjang_id'])) {
            $query->where('jenjang_id', $filters['jenjang_id']);
        }

        if (!empty($filters['tipe_kelas'])) {
            $query->where('tipe_kelas', $filters['tipe_kelas']);
        }

        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        return $query->orderBy($sortField, $sortDir)->paginate($filters['per_page'] ?? 15);
    }

    public function storeKelas(array $data)
    {
        // Generate kode_kelas unique
        $data['kode_kelas'] = $this->generateKodeKelas();

        return Kelas::create($data);
    }

    public function updateKelas(Kelas $kelas, array $data)
    {
        $kelas->update($data);
        return $kelas;
    }

    public function deleteKelas(Kelas $kelas)
    {
        // Soft delete
        $kelas->delete();
    }

    public function showKelas(Kelas $kelas)
    {
        // Must load enrollments to show members in Detail Page
        return $kelas->load(['program', 'jenjang', 'creator', 'enrollments.murid', 'enrollments.paket'])->loadCount('enrollments');
    }

    public function addAnggota(Kelas $kelas, array $data)
    {
        $enrollmentIds = $data['enrollment_ids'] ?? [];
        // Validate enrollments
        $enrollments = Enrollment::whereIn('id', $enrollmentIds)->get();

        if ($enrollments->count() !== count($enrollmentIds)) {
            throw ValidationException::withMessages(['enrollment_ids' => 'Salah satu enrollment tidak ditemukan.']);
        }

        DB::beginTransaction();
        try {
            foreach ($enrollments as $enrollment) {
                // Rule 1: Status Enrollment Aktif
                if ($enrollment->status !== 'Aktif') {
                    throw ValidationException::withMessages(['enrollment_ids' => "Enrollment {$enrollment->id} tidak aktif."]);
                }

                // Rule 2: Konsistensi Katalog
                if ($enrollment->program_id != $kelas->program_id) {
                    throw ValidationException::withMessages(['enrollment_ids' => "Enrollment {$enrollment->id} memiliki program berbeda."]);
                }
                if ($enrollment->jenjang_id != $kelas->jenjang_id) {
                    throw ValidationException::withMessages(['enrollment_ids' => "Enrollment {$enrollment->id} memiliki jenjang berbeda."]);
                }

                // Rule 3: Kapasitas
                if ($kelas->kapasitas) {
                    $currentCount = $kelas->enrollments()->wherePivot('status_anggota', 'Aktif')->count();
                    // Note: This check inside loop is naive for bulk, but sufficient if bulk size is small.
                    // Better: count all + incoming size.
                    // For now, assuming standard usage.
                    if ($currentCount >= $kelas->kapasitas) {
                        throw ValidationException::withMessages(['kapasitas' => 'Kapasitas kelas penuh.']);
                    }
                }

                // Rule 4: Private Individu
                if ($kelas->tipe_kelas === 'PRIVATE' && $kelas->mode_private === 'INDIVIDU') {
                    $currentCount = $kelas->enrollments()->wherePivot('status_anggota', 'Aktif')->count();
                    if ($currentCount >= 1) {
                        throw ValidationException::withMessages(['mode_private' => 'Kelas Private Individu hanya boleh 1 anggota.']);
                    }
                }

                $tanggalMasuk = isset($data['tanggal_masuk'])
                    ? \Carbon\Carbon::parse($data['tanggal_masuk'])->toDateString()
                    : date('Y-m-d');

                // Rule 5: Duplicate Membership
                $exists = $kelas->enrollments()->where('enrollment_id', $enrollment->id)->exists();
                if ($exists) {
                    $pivot = $kelas->enrollments()->where('enrollment_id', $enrollment->id)->first();
                    if ($pivot->pivot->status_anggota === 'Aktif') {
                        throw ValidationException::withMessages(['enrollment_ids' => "Enrollment {$enrollment->id} sudah ada di kelas ini."]);
                    } else {
                        // Re-activate
                        $kelas->enrollments()->updateExistingPivot($enrollment->id, [
                            'status_anggota' => 'Aktif',
                            'tanggal_masuk' => $tanggalMasuk,
                            'tanggal_keluar' => null
                        ]);
                        continue;
                    }
                }

                // Rule 6: Joining Date vs Class Period
                if ($kelas->periode_mulai && $tanggalMasuk < $kelas->periode_mulai->toDateString()) {
                    throw ValidationException::withMessages([
                        'tanggal_masuk' => "Tanggal masuk tidak boleh lebih awal dari periode mulai kelas (" . $kelas->periode_mulai->format('d-m-Y') . ")."
                    ]);
                }

                // Attach
                $kelas->enrollments()->attach($enrollment->id, [
                    'status_anggota' => 'Aktif',
                    'tanggal_masuk' => $tanggalMasuk,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $kelas->load('enrollments');
    }

    public function removeAnggota(Kelas $kelas, $enrollmentId)
    {
        // Soft remove
        $kelas->enrollments()->updateExistingPivot($enrollmentId, [
            'status_anggota' => 'Keluar',
            'tanggal_keluar' => now()
        ]);
    }

    private function generateKodeKelas()
    {
        // Simple generation: K + Ymd + Random
        do {
            $code = 'K-' . date('ymd') . '-' . strtoupper(Str::random(4));
        } while (Kelas::where('kode_kelas', $code)->exists());
        return $code;
    }
}
