@extends('exports.layout')

@section('title', 'Slip Gaji - ' . $payroll->employee->user->name)
@section('document_title', 'SLIP GAJI KARYAWAN')

@section('content')
<div style="margin-bottom: 20px;">
    <table style="width: 100%; border: none; margin-bottom: 10px;">
        <tr>
            <td style="width: 50%; border: none; padding: 0; vertical-align: top;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="width: 120px; border: none; padding: 2px; color: #666; font-size: 9pt;">Nama Karyawan
                        </td>
                        <td style="border: none; padding: 2px; font-weight: bold;">: {{ $payroll->employee->user->name
                            }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px; color: #666; font-size: 9pt;">ID Karyawan</td>
                        <td style="border: none; padding: 2px; font-weight: bold;">: {{
                            $payroll->employee->kode_karyawan }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px; color: #666; font-size: 9pt;">Jabatan/Divisi</td>
                        <td style="border: none; padding: 2px;">: {{ $payroll->employee->kategori_karyawan }} - {{
                            $payroll->employee->divisi }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; border: none; padding: 0; vertical-align: top;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="width: 120px; border: none; padding: 2px; color: #666; font-size: 9pt;">Periode Gaji
                        </td>
                        <td style="border: none; padding: 2px; font-weight: bold;">: {{
                            \Carbon\Carbon::createFromDate(null, $payroll->bulan, 1)->format('F') }} {{ $payroll->tahun
                            }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px; color: #666; font-size: 9pt;">Status Pembayaran</td>
                        <td style="border: none; padding: 2px;">:
                            <span
                                style="color: {{ $payroll->status === 'paid' ? '#059669' : '#D97706' }}; font-weight: bold; text-transform: uppercase;">
                                {{ $payroll->status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px; color: #666; font-size: 9pt;">Tanggal Cetak</td>
                        <td style="border: none; padding: 2px;">: {{ now()->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div style="display: table; width: 100%; margin-bottom: 20px;">
    <!-- Column Pendapatan -->
    <div style="display: table-cell; width: 48%; vertical-align: top;">
        <h3
            style="font-size: 10pt; color: #C8102E; border-bottom: 1px solid #C8102E; padding-bottom: 5px; margin-bottom: 10px;">
            PENERIMAAN / EARNINGS</h3>
        <table style="width: 100%; margin-bottom: 10px;">
            @foreach($payroll->detail_pendapatan as $item)
            <tr>
                <td style="border: none; border-bottom: 1px solid #f3f4f6; padding: 6px 0; font-size: 9pt;">{{
                    $item['jenis'] }}</td>
                <td
                    style="border: none; border-bottom: 1px solid #f3f4f6; padding: 6px 0; text-align: right; font-weight: bold;">
                    Rp {{ number_format($item['nilai'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #fef2f2;">
                <td style="border: none; padding: 8px 0; font-weight: bold; color: #C8102E;">Total Penerimaan</td>
                <td style="border: none; padding: 8px 0; text-align: right; font-weight: bold; color: #C8102E;">Rp {{
                    number_format($payroll->gaji_pokok + $payroll->total_fee_mengajar, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div style="display: table-cell; width: 4%;"></div>

    <!-- Column Potongan -->
    <div style="display: table-cell; width: 48%; vertical-align: top;">
        <h3
            style="font-size: 10pt; color: #4B5563; border-bottom: 1px solid #4B5563; padding-bottom: 5px; margin-bottom: 10px;">
            POTONGAN / DEDUCTIONS</h3>
        <table style="width: 100%; margin-bottom: 10px;">
            @forelse($payroll->detail_potongan as $item)
            <tr>
                <td style="border: none; border-bottom: 1px solid #f3f4f6; padding: 6px 0; font-size: 9pt;">
                    {{ ucfirst($item['jenis']) }} ({{ $item['jumlah_hari'] }} hari)
                </td>
                <td
                    style="border: none; border-bottom: 1px solid #f3f4f6; padding: 6px 0; text-align: right; font-weight: bold; color: #dc2626;">
                    -Rp {{ number_format($item['total'], 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2"
                    style="border: none; border-bottom: 1px solid #f3f4f6; padding: 15px 0; text-align: center; color: #9ca3af; font-style: italic; font-size: 8pt;">
                    Tidak ada potongan
                </td>
            </tr>
            @endforelse
            <tr style="background-color: #f9fafb;">
                <td style="border: none; padding: 8px 0; font-weight: bold;">Total Potongan</td>
                <td style="border: none; padding: 8px 0; text-align: right; font-weight: bold; color: #dc2626;">-Rp {{
                    number_format($payroll->total_potongan, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</div>

<div style="background-color: #111827; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
    <table style="width: 100%; border: none; margin-bottom: 0;">
        <tr>
            <td style="border: none; color: #9ca3af; font-weight: bold; font-size: 10pt;">TOTAL GAJI BERSIH (TAKE HOME
                PAY)</td>
            <td style="border: none; text-align: right; font-size: 16pt; font-weight: 900; color: #10b981;">
                Rp {{ number_format($payroll->gaji_bersih, 0, ',', '.') }}
            </td>
        </tr>
    </table>
</div>

<div style="margin-top: 40px;">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="width: 60%; border: none;">
                <p style="font-size: 8pt; color: #666; font-style: italic;">
                    * Slip gaji ini dihasilkan secara otomatis oleh sistem Shine Education Bali.<br>
                    Harap simpan slip ini sebagai bukti penerimaan gaji yang sah.
                </p>
            </td>
            <td style="width: 40%; border: none; text-align: center;">
                <p style="font-size: 9pt; margin-bottom: 40px;">Hormat Kami,</p>
                <p style="font-weight: bold; text-decoration: underline;">Manajemen Shine Education Bali</p>
            </td>
        </tr>
    </table>
</div>
@endsection