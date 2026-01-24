<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RekapBulananResource extends JsonResource
{
    protected $summary;

    public function __construct($resource, $summary = null)
    {
        parent::__construct($resource);
        $this->summary = $summary;
    }

    public function toArray(Request $request): array
    {
        // If summary is passed explicitly (list view where calculation happens in controller/service loop)
        // Or if attached to resource via some other means.
        // For list view, we likely passed [employee, summary] or just employee and computed summary?
        // Let's assume the controller attaches the computed summary to the employee object temporarily
        // or we pass it in constructor.

        $s = $this->summary ?? $this->resource->summary ?? [];

        return [
            'employee' => [
                'id' => $this->id,
                'nama' => $this->user->name ?? $this->nama_lengkap ?? 'Unknown',
                'kode_karyawan' => $this->kode_karyawan,
                'kategori_karyawan' => $this->kategori_karyawan,
                'subtipe_kontrak' => $this->subtipe_kontrak,
                'tipe_gaji' => $this->tipe_gaji,
                'status' => $this->status,
            ],
            'absensi' => [
                'hadir' => $s['absensi']['total_hadir'] ?? 0,
                'izin' => $s['absensi']['total_izin'] ?? 0,
                'sakit' => $s['absensi']['total_sakit'] ?? 0,
                'total_durasi_menit' => $s['absensi']['total_durasi_menit'] ?? 0,
            ],
            'cuti' => [
                'cuti_disetujui' => $s['cuti']['cuti_disetujui'] ?? 0,
                'izin_disetujui' => $s['cuti']['izin_disetujui'] ?? 0,
                'sakit_disetujui' => $s['cuti']['sakit_disetujui'] ?? 0,
            ],
            'jadwal' => [
                'sesi_terlaksana' => $s['jadwal']['sesi_terlaksana'] ?? 0,
                'sesi_digantikan' => $s['jadwal']['sesi_digantikan'] ?? 0,
                'sesi_menggantikan' => $s['jadwal']['sesi_menggantikan'] ?? 0,
                'total_realisasi_entry' => $s['jadwal']['total_realisasi_entry'] ?? 0,
            ],
            // 'payroll_preview' => optional (could be calculated if needed, likely heavy for list)
        ];
    }
}
