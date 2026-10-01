<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $payslip->slip_number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #0c4a6e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 9px;
            color: #64748b;
        }
        .doc-title {
            text-align: right;
            vertical-align: middle;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 16px;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .doc-title p {
            margin: 2px 0 0 0;
            font-size: 10px;
            font-weight: bold;
            color: #0284c7;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 6px;
            vertical-align: top;
            font-size: 10px;
        }
        .info-label {
            width: 15%;
            color: #64748b;
            font-weight: 600;
        }
        .info-sep {
            width: 2%;
            color: #64748b;
        }
        .info-val {
            width: 33%;
            font-weight: 700;
            color: #0f172a;
        }
        .financial-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .financial-col {
            width: 50%;
            vertical-align: top;
        }
        .component-table {
            width: 100%;
            border-collapse: collapse;
        }
        .component-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 10px;
            font-weight: 700;
            padding: 6px 8px;
            text-transform: uppercase;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }
        .component-table td {
            padding: 6px 8px;
            font-size: 10px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .subtotal-row td {
            font-weight: 700;
            background-color: #f8fafc;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
        }
        .net-pay-box {
            background-color: #082f49;
            color: #ffffff;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .net-pay-table {
            width: 100%;
        }
        .net-pay-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #7dd3fc;
        }
        .net-pay-amount {
            font-size: 18px;
            font-weight: 800;
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }
        .signatures-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .sig-col {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            font-size: 10px;
        }
        .qr-box {
            text-align: center;
        }
        .qr-box img {
            width: 75px;
            height: 75px;
        }
        .footer-note {
            margin-top: 25px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>
<body>

    <!-- Header Dokumen -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="company-name">PT NEXUS HRIS INDONESIA</div>
                <div class="company-sub">
                    Gedung Cyber Tower Lt. 12, Jl. HR Rasuna Said, Jakarta Selatan 12950<br>
                    NPWP: 01.345.678.9-012.000 &bull; Telp: (021) 555-8899 &bull; Website: www.nexushris.co.id
                </div>
            </td>
            <td class="doc-title" style="width: 40%;">
                <h2>SLIP GAJI RESMI</h2>
                <p>PERIODE: {{ \Carbon\Carbon::create($payslip->payrollBatch->year, $payslip->payrollBatch->month, 1)->translatedFormat('F Y') }}</p>
                <div style="font-size: 9px; color: #64748b; font-family: monospace;">NO: {{ $payslip->slip_number }}</div>
            </td>
        </tr>
    </table>

    <!-- Profil Karyawan -->
    <table class="info-table">
        <tr>
            <td class="info-label">NIP</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono">{{ $payslip->employee->employee_code }}</td>
            <td class="info-label">Departemen</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $payslip->employee->department->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Nama Karyawan</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $payslip->employee->user->name }}</td>
            <td class="info-label">Jabatan</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $payslip->employee->designation->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Cabang Penempatan</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $payslip->employee->branch->name ?? '-' }}</td>
            <td class="info-label">Rekening Bank</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono">{{ $payslip->employee->bank_name ?? 'BCA' }} - {{ $payslip->bank_account_no ?? ($payslip->employee->bank_account_no ?? '-') }}</td>
        </tr>
        <tr>
            <td class="info-label">Status Hubungan Kerja</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $payslip->employee->employment_status }}</td>
            <td class="info-label">Tanggal Bayar</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ \Carbon\Carbon::parse($payslip->payrollBatch->payment_date)->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <!-- Tabel Komponen Gaji 2 Kolom -->
    <table class="financial-container">
        <tr>
            <!-- Kolom Kiri: PENDAPATAN (EARNINGS) -->
            <td class="financial-col" style="padding-right: 8px;">
                <table class="component-table">
                    <thead>
                        <tr>
                            <th>A. Pendapatan (Earnings)</th>
                            <th class="text-right">Jumlah (IDR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $earnings = $payslip->items->where('component_type', 'EARNING');
                            $gross = $earnings->sum('amount');
                        @endphp
                        @foreach($earnings as $item)
                            <tr>
                                <td>{{ $item->component_name }}</td>
                                <td class="text-right font-mono">{{ number_format($item->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="subtotal-row">
                            <td style="color: #0c4a6e;">TOTAL PENDAPATAN KOTOR (A)</td>
                            <td class="text-right font-mono" style="color: #0c4a6e;">Rp {{ number_format($gross, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>

            <!-- Kolom Kanan: POTONGAN (DEDUCTIONS) -->
            <td class="financial-col" style="padding-left: 8px;">
                <table class="component-table">
                    <thead>
                        <tr>
                            <th>B. Potongan (Deductions)</th>
                            <th class="text-right">Jumlah (IDR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $deductions = $payslip->items->where('component_type', 'DEDUCTION');
                            $totalDeductions = $deductions->sum('amount');
                        @endphp
                        @foreach($deductions as $item)
                            <tr>
                                <td>{{ $item->component_name }}</td>
                                <td class="text-right font-mono" style="color: #b91c1c;">- {{ number_format($item->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="subtotal-row">
                            <td style="color: #991b1b;">TOTAL POTONGAN (B)</td>
                            <td class="text-right font-mono" style="color: #991b1b;">- Rp {{ number_format($totalDeductions, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <!-- Box Take Home Pay -->
    <div class="net-pay-box">
        <table class="net-pay-table">
            <tr>
                <td>
                    <div class="net-pay-label">GAJI BERSIH DITERIMA (TAKE-HOME PAY) = (A - B)</div>
                    <div style="font-size: 9px; color: #bae6fd; margin-top: 2px;">Dana telah ditransfer ke rekening karyawan pada tanggal pembayaran resmi.</div>
                </td>
                <td class="net-pay-amount">
                    Rp {{ number_format($payslip->net_salary, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Tanda Tangan & QR Code Verifikasi Keabsahan -->
    <table class="signatures-table">
        <tr>
            <td class="sig-col">
                <div style="color: #64748b; margin-bottom: 45px;">Diterima Oleh Karyawan,</div>
                <div style="font-weight: 700; text-decoration: underline;">{{ $payslip->employee->user->name }}</div>
                <div style="font-size: 8px; color: #64748b;">NIP: {{ $payslip->employee->employee_code }}</div>
            </td>
            <td class="sig-col qr-box">
                @if(!empty($qrCodeSvg))
                    <img src="data:image/svg+xml;base64,{{ $qrCodeSvg }}" alt="QR Code Verifikasi">
                    <div style="font-size: 8px; color: #64748b; margin-top: 4px; font-weight: 600;">Scan Verifikasi Keaslian</div>
                @endif
            </td>
            <td class="sig-col">
                <div style="color: #64748b; margin-bottom: 45px;">Jakarta, {{ \Carbon\Carbon::parse($payslip->payrollBatch->payment_date)->translatedFormat('d F Y') }}<br>Finance & Payroll Department,</div>
                <div style="font-weight: 700; text-decoration: underline;">NexusHRIS Corporate Management</div>
                <div style="font-size: 8px; color: #64748b;">Sistem Penggajian Terintegrasi</div>
            </td>
        </tr>
    </table>

    <!-- Footer Catatan Kerahasiaan -->
    <div class="footer-note">
        Pemberitahuan Rahasia: Dokumen slip gaji ini bersifat rahasia dan diterbitkan secara digital oleh NexusHRIS Enterprise. Segala informasi gaji karyawan tunduk pada kebijakan privasi perusahaan dan regulasi ketenagakerjaan yang berlaku.
    </div>

</body>
</html>
