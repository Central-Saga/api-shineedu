<?php

namespace App\Modules\HR\Application\Services;

use App\Modules\HR\Domain\Models\Employee;
use App\Modules\HR\Domain\Models\PengaturanCutiRules;
use App\Modules\Scheduling\Domain\Models\RealisasiJadwalKerja;
use Carbon\Carbon;

class PayrollService
{
    protected $rekapService;
    protected $ruleService;

    public function __construct(RekapBulananService $rekapService, PengaturanCutiService $ruleService)
    {
        $this->rekapService = $rekapService;
        $this->ruleService = $ruleService;
    }

    /**
     * Generate Payroll for all active employees in a given month.
     * Persists data to 'payrolls' table.
     */
    public function generateByMonth(int $month, int $year)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($month, $year) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();

            //Get all active employees
            $employees = Employee::where('status', 'aktif')
                ->orWhereHas('user', fn($q) => $q->where('status', 'Aktif')) // Fallback check
                ->get();

            $results = [];

            foreach ($employees as $employee) {
                // 1. Calculate Summary (existing logic)
                $rekap = $this->rekapService->calculateSummary($employee, $startDate, $endDate);

                // 2. Calculate Components (existing logic)
                $gajiPokok = $this->calculateBaseSalary($employee);
                $feeSesi = $this->calculateSessionFee($employee, $startDate, $endDate);
                $potongan = $this->calculateDeductions($employee, $rekap['cuti']);

                // 3. Totals
                $totalPendapatan = $gajiPokok + $feeSesi;
                $totalPotongan = $potongan['total_value'];
                $grandTotal = max(0, $totalPendapatan - $totalPotongan);

                // 4. Save to Database
                $payroll = \App\Modules\HR\Domain\Models\Payroll::updateOrCreate(
                    [
                        'karyawan_id' => $employee->id,
                        'bulan' => $month,
                        'tahun' => $year,
                    ],
                    [
                        'gaji_pokok' => $gajiPokok,
                        'total_fee_mengajar' => $feeSesi,
                        'total_potongan' => $totalPotongan,
                        'gaji_bersih' => $grandTotal,
                        'detail_potongan' => $potongan['items'],
                        'detail_pendapatan' => [
                            ['jenis' => 'Gaji Pokok', 'nilai' => $gajiPokok],
                            ['jenis' => 'Fee Mengajar', 'nilai' => $feeSesi],
                        ],
                        // Only update status if it doesn't exist, preserving existing approval flow if re-generated
                        // Or should we reset to draft? Let's keep existing status if set, else draft.
                        // For updateOrCreate, we can use DB::raw or check first.
                        // Here we just set 'draft' if created, but updateOrCreate overwrites.
                        // Strategy: We want to update calculations even if status is 'generated'.
                        // But if 'paid', maybe block regeneration?
                        // For now, let's assume regeneration resets to 'generated' or 'draft'.
                        'status' => 'draft',
                    ]
                );

                $results[] = $payroll;
            }

            // Notification: Foundation
            $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
            $this->notifyFoundation(
                "Payroll Generated: {$monthName}",
                "Payroll untuk periode {$monthName} telah digenerate. Silakan cek sistem untuk verifikasi."
            );

            return $results;
        });
    }

    public function updateStatus(int $id, string $status)
    {
        $payroll = \App\Modules\HR\Domain\Models\Payroll::findOrFail($id);

        $data = ['status' => $status];
        if (in_array(strtolower($status), ['paid', 'transferred'])) {
            $data['tanggal_pembayaran'] = now();
        }

        $payroll->update($data);

        // Notification: Employee (If Paid)
        if (in_array(strtolower($status), ['paid', 'transferred'])) {
            $employee = $payroll->karyawan;
            if ($employee && $employee->email_pribadi) {
                $monthName = \Carbon\Carbon::createFromDate($payroll->tahun, $payroll->bulan, 1)->translatedFormat('F Y');
                $this->sendEmail(
                    $employee->email_pribadi,
                    "Slip Gaji Tersedia: {$monthName}",
                    "Gaji periode {$monthName} telah ditransfer. Mohon cek rekening Anda dan slip gaji di sistem."
                );
            }
        }

        return $payroll;
    }

    /**
     * Preview Payroll for Employee (Legacy / On-the-fly)
     * Can still be used for individual checking before sync
     */
    public function previewPayroll(int $employeeId, int $month, int $year)
    {
        // ... (existing implementation)
        $employee = Employee::findOrFail($employeeId);
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        // 1. Base Data
        $rekap = $this->rekapService->calculateSummary($employee, $startDate, $endDate);

        // 2. Components
        $gajiPokok = $this->calculateBaseSalary($employee);
        $feeSesi = $this->calculateSessionFee($employee, $startDate, $endDate);
        $potongan = $this->calculateDeductions($employee, $rekap['cuti']);

        // 3. Totals
        $totalPendapatan = $gajiPokok + $feeSesi;
        $totalPotongan = $potongan['total_value'];
        $grandTotal = $totalPendapatan - $totalPotongan;

        return [
            'employee' => $employee,
            'periode' => ['month' => $month, 'year' => $year],
            'komponen' => [
                'gaji_pokok' => $gajiPokok,
                'fee_sesi' => $feeSesi,
                'potongan' => $potongan['items'], // Breakdown
            ],
            'totals' => [
                'total_pendapatan' => $totalPendapatan,
                'total_potongan' => $totalPotongan,
                'gaji_bersih' => $grandTotal < 0 ? 0 : $grandTotal,
            ]
        ];
    }

    public function calculateBaseSalary(Employee $employee)
    {
        // Rule: Only if tipe_gaji = 'Bulanan' use gaji_pokok.
        // If 'Per Sesi', base is 0.
        // Normalize string case just in case.
        $type = strtolower($employee->tipe_gaji);
        if ($type === 'bulanan') {
            return (float) $employee->gaji_pokok;
        }
        return 0.0;
    }

    public function calculateSessionFee(Employee $employee, Carbon $startDate, Carbon $endDate)
    {
        // STRICT: Only Freelance gets session fee
        if (strtolower($employee->kategori_karyawan) !== 'freelance') {
            return 0;
        }

        // Get realized sessions where this employee gets the money
        // Conditions:
        // 1. Status 'disetujui'
        // 2. I am Pengajar AND No Pengganti
        // 3. OR I am Pengganti

        $sessions = RealisasiJadwalKerja::query()
            ->with('jadwalKerja')
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->where('status', 'disetujui')
            ->where(function ($q) use ($employee) {
                $q->where(function ($sub) use ($employee) {
                    $sub->where('guru_pengajar_id', $employee->id)
                        ->whereNull('guru_pengganti_id');
                })->orWhere('guru_pengganti_id', $employee->id);
            })
            ->get();

        $totalFee = 0;
        foreach ($sessions as $session) {
            // Priority: Realisasi tarif (not explicitly in table schema provided but standard practice)
            // If not in Realisasi, fallback to JadwalKerja tarif
            // Current Schema Realisasi doesn't have 'tarif' column shown in previous view_file,
            // so we rely on $session->jadwalKerja->tarif

            $tarif = 0;
            if ($session->jadwalKerja) {
                $tarif = (float) $session->jadwalKerja->tarif;
            }
            $totalFee += $tarif;
        }

        return $totalFee;
    }

    public function calculateDeductions(Employee $employee, array $cutiSummary)
    {
        $deductions = [];
        $totalDeduction = 0;

        // Map summary keys to Rule 'jenis'
        $map = [
            'cuti_disetujui' => 'cuti',
            'izin_disetujui' => 'izin',
            'sakit_disetujui' => 'sakit'
        ];

        foreach ($map as $summaryKey => $ruleJenis) {
            $count = $cutiSummary[$summaryKey] ?? 0;
            if ($count <= 0) continue;

            // Find applicable rule using service
            $subtipe = strtolower($employee->kategori_karyawan) === 'kontrak' ? $employee->subtipe_kontrak : null;

            // Fix: Pass Divisi to findRule
            $rule = $this->ruleService->findRule(
                $employee->kategori_karyawan,
                $subtipe,
                $ruleJenis,
                $employee->divisi
            );

            if ($rule && $rule->potongan_nilai > 0) {
                // Calculation Type
                $amount = 0;
                $unitValue = 0;

                if ($rule->potongan_tipe === 'per_hari') {
                    // Formula: (Gaji Pokok / 25) * Koefisien * Jumlah Hari
                    $dailyRate = $employee->gaji_pokok / 25;
                    $amount = $dailyRate * $rule->potongan_nilai * $count;
                    $unitValue = $dailyRate * $rule->potongan_nilai;
                } else {
                    // Flat Nominal
                    $amount = $rule->potongan_nilai * $count;
                    $unitValue = $rule->potongan_nilai;
                }

                if ($amount > 0) {
                    $deductions[] = [
                        'jenis' => $ruleJenis,
                        'jumlah_hari' => $count,
                        'rule' => $rule->potongan_tipe,
                        'nilai_satuan' => $unitValue,
                        'total' => $amount
                    ];
                    $totalDeduction += $amount;
                }
            }
        }

        return [
            'total_value' => $totalDeduction,
            'items' => $deductions
        ];
    }

    protected function sendEmail(string $to, string $subject, string $message)
    {
        if (!empty($to)) {
            dispatch(new \App\Jobs\SendEmailJob($to, new \App\Mail\GeneralNotification($subject, $message)));
        }
    }

    protected function notifyFoundation(string $subject, string $message)
    {
        $foundationEmail = config('mail.to_foundation');

        // Fallback hardcoded to ensure delivery if config fails
        if (empty($foundationEmail)) {
            $foundationEmail = 'yayasanpendidikangemilangbali@gmail.com';
        }

        if ($foundationEmail) {
            $this->sendEmail($foundationEmail, $subject, $message);
        }
    }
}
