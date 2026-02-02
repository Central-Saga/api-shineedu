<?php

namespace App\Modules\Enrollment\Application\Services;

use App\Modules\Catalog\Application\Services\PricingService;
use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Enrollment\Domain\Models\PaketMurid;
use App\Modules\Enrollment\Domain\Models\PaketMuridLedger;
use App\Modules\Student\Application\Services\MuridService;
use App\Modules\Student\Domain\Models\Murid;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class EnrollmentService
{
    protected $pricingService;
    protected $muridService;

    public function __construct(PricingService $pricingService, MuridService $muridService)
    {
        $this->pricingService = $pricingService;
        $this->muridService = $muridService;
    }

    /**
     * Get list of enrollments with filters.
     */
    public function list(array $params): LengthAwarePaginator
    {
        $query = Enrollment::query()
            ->with(['murid', 'program', 'jenjang', 'paket', 'creator']);

        // Search by Enrollment Code or Murid Name
        if (! empty($params['q'] ?? null)) {
            $keyword = (string) $params['q'];
            $query->where(function ($q) use ($keyword) {
                $q->where('kode_enrollment', 'like', "%{$keyword}%")
                    ->orWhereHas('murid', function ($m) use ($keyword) {
                        $m->where('nama_lengkap', 'like', "%{$keyword}%");
                    });
            });
        }

        // Filters
        if (! empty($params['status'] ?? null)) {
            $query->where('status', $params['status']);
        }
        if (! empty($params['program_id'] ?? null)) {
            $query->where('program_id', $params['program_id']);
        }
        if (! empty($params['jenjang_id'] ?? null)) {
            $query->where('jenjang_id', $params['jenjang_id']);
        }
        if (! empty($params['paket_id'] ?? null)) {
            $query->where('paket_id', $params['paket_id']);
        }
        if (! empty($params['murid_id'] ?? null)) {
            $query->where('murid_id', $params['murid_id']);
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDir = $params['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($params['per_page'] ?? 15);
    }

    /**
     * Create a new enrollment.
     * Handles inline Murid creation and Price Lookup.
     */
    public function create(array $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            // 1. Handle Murid (Existing OR New)
            $muridId = $data['murid_id'] ?? null;
            $muridBaruData = $data['murid_baru'] ?? null;

            if (! $muridId && $muridBaruData) {
                // Inline create murid
                // Add jenjang_id to murid data from the enrollment data
                $muridBaruData['jenjang_id'] = $data['jenjang_id'];
                $murid = $this->muridService->create($muridBaruData);
                $muridId = $murid->id;
            } elseif ($muridId) {
                // Optional: Check if murid exists and is active
                $murid = Murid::findOrFail($muridId);
                // if ($murid->status !== 'Aktif') throw new Exception("Murid tidak aktif");
            } else {
                throw new Exception("Murid ID or Murid Baru data is required.");
            }

            // 2. Lookup Price
            $programId = $data['program_id'];
            $jenjangId = $data['jenjang_id'];
            $paketId = $data['paket_id'];
            $jumlahSiswa = $data['jumlah_siswa'] ?? 1;
            $tanggalMulai = $data['tanggal_mulai'] ?? date('Y-m-d');

            $priceRule = $this->pricingService->lookupPrice(
                $programId,
                $jenjangId,
                $paketId,
                $jumlahSiswa,
                $tanggalMulai
            );

            if (! $priceRule) {
                throw new Exception("Harga tidak ditemukan untuk kombinasi Program/Jenjang/Paket/Jumlah Siswa tersebut.", 422);
            }

            // Registration Fee Logic
            $regFeeAmount = $data['biaya_pendaftaran_amount'] ?? 0;
            $regFeeStatus = ($regFeeAmount > 0) ? 'UNPAID' : 'WAIVED';

            // 3. Create Enrollment
            $enrollment = Enrollment::create([
                'kode_enrollment' => $this->generateKodeEnrollment(),
                'murid_id' => $muridId,
                'program_id' => $programId,
                'jenjang_id' => $jenjangId,
                'paket_id' => $paketId,
                'jumlah_siswa' => $jumlahSiswa,
                'harga_final' => $priceRule->harga, // Snapshot price!
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                'status' => 'Aktif',
                'catatan' => $data['catatan'] ?? null,
                'biaya_pendaftaran_amount' => $regFeeAmount,
                'biaya_pendaftaran_status' => $data['biaya_pendaftaran_status'] ?? $regFeeStatus,
                'biaya_pendaftaran_due_date' => $data['biaya_pendaftaran_due_date'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // 4. Create Initial Paket Murid (with saldo 0 - saldo will be added after payment)
            $paket = Paket::find($paketId);
            if ($paket && $paket->pertemuan_per_bulan > 0) {
                PaketMurid::create([
                    'enrollment_id' => $enrollment->id,
                    'paket_id' => $paketId,
                    'status' => 'AKTIF',
                    'tanggal_mulai' => $tanggalMulai,
                    'created_by' => auth()->id(),
                ]);
                // NOTE: Saldo pertemuan akan di-topup setelah pembayaran pertama (pendaftaran + paket)
            }

            // Notification: Foundation
            $muridName = $murid ? $murid->nama_lengkap : 'Unknown';
            $programName = $enrollment->program ? $enrollment->program->nama : 'Unknown Program';

            $this->notifyFoundation(
                "Pendaftaran Baru: {$muridName}",
                "Siswa baru atas nama {$muridName} telah mendaftar di program {$programName}. Mohon verifikasi data."
            );

            return $enrollment;
        });
    }

    /**
     * Show enrollment details.
     */
    public function show(Enrollment $enrollment): Enrollment
    {
        return $enrollment->load(['murid', 'program', 'jenjang', 'paket', 'creator', 'kelas.schedules.guru.user']);
    }

    /**
     * Update enrollment.
     * Note: Typically we don't re-calculate price on simple updates unless explicitly requested.
     */
    public function update(Enrollment $enrollment, array $data): Enrollment
    {
        // Prevent changing pricing critical fields without explicit logic if needed.
        // For now, allow standard update.

        $enrollment->update($data);
        return $enrollment;
    }

    /**
     * Delete enrollment.
     */
    public function delete(Enrollment $enrollment): void
    {
        $enrollment->delete();
    }

    /**
     * Update Registration Fee Status.
     */
    public function updateRegistrationFeeStatus(Enrollment $enrollment, array $data): Enrollment
    {
        $enrollment->update([
            'biaya_pendaftaran_status' => $data['status'],
            'biaya_pendaftaran_due_date' => $data['due_date'] ?? $enrollment->biaya_pendaftaran_due_date,
        ]);

        return $enrollment;
    }

    protected function generateKodeEnrollment(): string
    {
        // Simple generation logic, can be improved.
        return 'ENR-' . date('ymd') . '-' . strtoupper(uniqid());
    }
    protected function sendEmail(string $to, string $subject, string $message)
    {
        if (!empty($to)) {
            dispatch(new \App\Jobs\SendEmailJob($to, new \App\Mail\GeneralNotification($subject, $message)));
        }
    }

    protected function notifyFoundation(string $subject, string $message)
    {
        $foundationEmail = env('MAIL_TO_FOUNDATION');
        if ($foundationEmail) {
            $this->sendEmail($foundationEmail, $subject, $message);
        }
    }
}
