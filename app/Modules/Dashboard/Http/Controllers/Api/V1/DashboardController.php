<?php

namespace App\Modules\Dashboard\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Student\Domain\Models\Murid;
use App\Modules\HR\Domain\Models\Employee;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\JsonResponse;
use App\Shared\Http\Responses\ApiResponse;

use App\Modules\Finance\Domain\Models\KasTransaksi;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        // 1. Total Murid
        $totalMurid = Murid::where('status', 'Aktif')->count();
        $newMuridThisMonth = Murid::where('status', 'Aktif')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();


        // 2. Total Guru
        $totalGuru = Employee::whereHas('user.roles', function ($q) {
            $q->where('name', 'like', '%Guru%')
                ->orWhere('name', 'like', '%Teacher%')
                ->orWhere('name', 'like', '%Pengajar%');
        })->where('status', \App\Modules\HR\Domain\Enums\EmployeeStatus::AKTIF)->count();

        // 3. Kelas Aktif (Active Enrollments)
        // Assuming 'Aktif' status in Enrollment means class is ongoing
        $kelasAktif = Enrollment::where('status', 'Aktif')->count();

        // 4. Calculate Estimasi Omset (Total Income This Month)
        $estimasiOmset = KasTransaksi::where('type', 'IN')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('amount');

        // 5. Activity Logs (Latest 5)
        $activities = Activity::with('causer')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'description' => $activity->description,
                    'causer' => $activity->causer ? $activity->causer->name : 'System',
                    'created_at' => $activity->created_at->diffForHumans(),
                    'event' => $activity->event,
                ];
            });

        // 6. Latest Enrollments (Summary Table)
        $latestEnrollments = Enrollment::with(['murid', 'program'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'murid_nama' => $enrollment->murid ? $enrollment->murid->nama_lengkap : 'Unknown',
                    'program_nama' => $enrollment->program ? $enrollment->program->nama : 'Unknown',
                    'status' => $enrollment->status,
                    'created_at' => $enrollment->created_at->format('d M Y'),
                ];
            });

        // 7. Chart Data (Monthly Income - Last 6 Months)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $income = KasTransaksi::where('type', 'IN')
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->sum('amount');

            $chartData[] = [
                'name' => $date->format('M'), // e.g. Jan, Feb
                'total' => (int) $income,
            ];
        }

        return ApiResponse::ok([
            'stats' => [
                'total_murid' => $totalMurid,
                'new_murid_this_month' => $newMuridThisMonth,
                'total_guru' => $totalGuru,
                'kelas_aktif' => $kelasAktif,
                'estimasi_omset' => $estimasiOmset
            ],
            'recent_activities' => $activities,
            'latest_enrollments' => $latestEnrollments,
            'chart_data' => $chartData
        ], 'Dashboard stats retrieved successfully');
    }
}
