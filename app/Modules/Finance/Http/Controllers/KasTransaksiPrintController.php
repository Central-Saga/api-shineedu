<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\KasTransaksi;
use Illuminate\Http\Request;

class KasTransaksiPrintController extends Controller
{
    /**
     * Generate thermal print receipt for a transaction
     *
     * GET /api/v2/kas/transaksi/{kasTransaksi}/print-thermal
     */
    public function printThermal(KasTransaksi $kasTransaksi)
    {
        // Load createdBy relationship
        $kasTransaksi->load('createdBy');

        // Load reference-specific relationships based on reference_type
        if ($kasTransaksi->reference_type === 'paket_topup' && $kasTransaksi->reference_id) {
            // Load paket topup with enrollment details
            $kasTransaksi->load([
                'paketTopup',
                'paketTopup.enrollment',
                'paketTopup.enrollment.murid',
                'paketTopup.enrollment.program',
                'paketTopup.enrollment.jenjang'
            ]);
        }

        // Debug: Log what we have
        \Log::info('Print Transaction', [
            'id' => $kasTransaksi->id,
            'reference_type' => $kasTransaksi->reference_type,
            'reference_id' => $kasTransaksi->reference_id,
            'has_paket_topup' => $kasTransaksi->paketTopup ? 'yes' : 'no',
            'has_enrollment' => $kasTransaksi->paketTopup?->enrollment ? 'yes' : 'no',
            'pihak' => $kasTransaksi->pihak,
        ]);

        // Generate thermal receipt HTML (58mm width)
        $html = view('finance.thermal-receipt', [
            'transaction' => $kasTransaksi,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    /**
     * Download PDF receipt for a transaction
     *
     * GET /api/v2/kas/transaksi/{kasTransaksi}/download-receipt
     */
    public function downloadReceipt(KasTransaksi $kasTransaksi)
    {
        // Load createdBy relationship
        $kasTransaksi->load('createdBy');

        // Load reference-specific relationships based on reference_type
        if ($kasTransaksi->reference_type === 'paket_topup' && $kasTransaksi->reference_id) {
            // Load paket topup with enrollment details
            $kasTransaksi->load([
                'paketTopup',
                'paketTopup.enrollment',
                'paketTopup.enrollment.murid',
                'paketTopup.enrollment.program',
                'paketTopup.enrollment.jenjang'
            ]);
        }

        $pdf = \PDF::loadView('finance.receipt-pdf', [
            'transaction' => $kasTransaksi,
        ]);

        $filename = 'kwitansi-' . $kasTransaksi->id . '-' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
