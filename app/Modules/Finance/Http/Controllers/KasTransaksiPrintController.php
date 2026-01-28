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
        // Load relationships
        $kasTransaksi->load([
            'enrollment.murid',
            'enrollment.program',
            'enrollment.jenjang',
            'createdBy',
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
        // Load relationships
        $kasTransaksi->load([
            'enrollment.murid',
            'enrollment.program',
            'enrollment.jenjang',
            'createdBy',
        ]);

        $pdf = \PDF::loadView('finance.receipt-pdf', [
            'transaction' => $kasTransaksi,
        ]);

        $filename = 'kwitansi-' . $kasTransaksi->id . '-' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
