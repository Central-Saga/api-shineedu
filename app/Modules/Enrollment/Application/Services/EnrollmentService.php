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
        // Filter by sumber (INTERNAL, ISELLER, IMPORT)
        if (! empty($params['sumber'] ?? null)) {
            $query->where('sumber', $params['sumber']);
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
                'sumber' => $data['sumber'] ?? 'INTERNAL',
                // saldo_override = null untuk internal enrollment (pakai sistem ledger normal)
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
     * Create enrollment from external system (i-seller/legacy)
     * untuk tracking absensi, logbook, dan sisa pertemuan saja
     * Pembayaran sudah lunas di sistem external
     * 
     * Opsi saldo:
     * - saldo_override = null: pakai sistem ledger (topup ke ledger)
     * - saldo_override >= 0: saldo manual (decrement saat absen)
     * - saldo_override = -1: unlimited (tidak dicek saldo)
     */
    public function createFromExternal(array $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            // 1. Handle Murid (Existing OR New)
            $muridId = $data['murid_id'] ?? null;
            $muridBaruData = $data['murid_baru'] ?? null;

            if (! $muridId && $muridBaruData) {
                // Inline create murid dari data i-seller
                $muridBaruData['jenjang_id'] = $data['jenjang_id'];
                $murid = $this->muridService->create($muridBaruData);
                $muridId = $murid->id;
            } elseif ($muridId) {
                $murid = Murid::findOrFail($muridId);
            } else {
                throw new Exception("Murid ID atau Murid Baru data wajib diisi.");
            }

            $programId = $data['program_id'];
            $jenjangId = $data['jenjang_id'];
            $paketId = $data['paket_id'];
            $jumlahSiswa = $data['jumlah_siswa'] ?? 1;
            $tanggalMulai = $data['tanggal_mulai'] ?? date('Y-m-d');

            // 2. Harga final (bisa dari input atau lookup)
            $hargaFinal = $data['harga_final'] ?? null;
            
            // Kalau harga_final tidak diisi, coba lookup
            if ($hargaFinal === null) {
                $priceRule = $this->pricingService->lookupPrice(
                    $programId,
                    $jenjangId,
                    $paketId,
                    $jumlahSiswa,
                    $tanggalMulai
                );
                $hargaFinal = $priceRule?->harga ?? 0;
            }

            // 3. Determine saldo_override mode
            $saldoOverride = $data['saldo_override'] ?? null;
            $jumlahPertemuan = $data['jumlah_pertemuan'] ?? null;
            
            // Jika saldo_override tidak di-set, tapi ada jumlah_pertemuan:
            // - Set saldo_override = jumlah_pertemuan (manual mode)
            if ($saldoOverride === null && $jumlahPertemuan !== null) {
                $saldoOverride = (int) $jumlahPertemuan;
            }

            // 4. Create Enrollment dengan flag external
            $enrollment = Enrollment::create([
                'kode_enrollment' => $data['kode_enrollment'] ?? $this->generateKodeEnrollment(),
                'murid_id' => $muridId,
                'program_id' => $programId,
                'jenjang_id' => $jenjangId,
                'paket_id' => $paketId,
                'jumlah_siswa' => $jumlahSiswa,
                'harga_final' => $hargaFinal,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' = $data['tanggal_selesai'] ?? null,
                'status' => 'Aktif',
                'catatan' => $data['catatan'] ?? null,
                // Registration fee langsung PAID karena sudah bayar di i-seller
                'biaya_pendaftaran_amount' => $data['biaya_pendaftaran_amount'] ?? 0,
                'biaya_pendaftaran_status' => 'PAID',
                'biaya_pendaftaran_due_date' => null,
                'created_by' => $data['created_by'] ?? auth()->id(),
                'sumber' => $data['sumber'] ?? 'ISELLER',
                'external_reference_id' => $data['external_reference_id'] ?? null,
                'saldo_override' => $saldoOverride,
            ]);

            // 5. Buat PaketMurid hanya jika pakai sistem ledger (saldo_override = null)
            // Kalau saldo_override di-set, pakai saldo manual dari enrollment.saldo_override
            if ($saldoOverride === null) {
                $paket = Paket::find($paketId);
                if ($paket && $paket->pertemuan_per_bulan > 0) {
                    $jumlahPertemuanLedger = $jumlahPertemuan ?? $paket->pertemuan_per_bulan ?? 0;
                    
                    if ($jumlahPertemuanLedger > 0) {
                        $paketMurid = PaketMurid::create([
                            'enrollment_id' => $enrollment->id,
                            'paket_id' => $paketId,
                            'status' => 'AKTIF',
                            'tanggal_mulai' => $tanggalMulai,
                            'tanggal_berakhir' => $data['tanggal_berakhir'] ?? null,
                            'catatan' => 'Import dari external: ' . ($data['sumber'] ?? 'ISELLER'),
                            'created_by' => auth()->id(),
                        ]);

                        // TOPUP ke ledger
                        PaketMuridLedger::create([
                            'paket_murid_id' => $paketMurid->id,
                            'tanggal' => $data['tanggal_pembayaran'] ?? now(),
                            'type' => PaketMuridLedger::TYPE_TOPUP,
                            'qty' => $jumlahPertemuanLedger,
                            'reference_type' => PaketMuridLedger::REF_PURCHASE,
                            'reference_id' => null,
                            'reason' => "Pembelian di " . ($data['sumber'] ?? 'i-seller') . " (Ref: " . ($data['external_reference_id'] ?? 'N/A') . ")",
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }

            // 6. Optional: Assign ke kelas jika sudah ada data kelas
            if (!empty($data['kelas_id'])) {
                $enrollment->kelas()->attach($data['kelas_id'], [
                    'status_anggota' => 'Aktif',
                    'tanggal_masuk' => $tanggalMulai,
                ]);
            }

            return $enrollment->load(['murid', 'paketMurid.ledger', 'kelas']);
        });
    }

    /**
     * Update saldo override untuk enrollment
     * Untuk admin/guru yang ingin update saldo manual
     * 
     * @param Enrollment $enrollment
     * @param int|null $saldoOverride null = pakai ledger, >=0 = manual, -1 = unlimited
     * @return Enrollment
     */
    public function updateSaldoOverride(Enrollment $enrollment, ?int $saldoOverride): Enrollment
    {
        $enrollment->update(['saldo_override' => $saldoOverride]);
        
        \Illuminate\Support\Facades\Log::info("Saldo override updated for enrollment {$enrollment->id}: " . 
            ($saldoOverride === null ? 'ledger mode' : 
            ($saldoOverride === -1 ? 'unlimited mode' : "manual saldo {$saldoOverride}")));
        
        return $enrollment;
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
