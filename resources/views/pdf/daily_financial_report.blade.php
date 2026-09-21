<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Finansial Harian Resmi</title>
    <style>
        @page {
            margin: 28px 32px 32px 32px;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
            font-size: 11pt;
            line-height: 1.45;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            border-bottom: 2.5px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .company-title {
            font-size: 16pt;
            font-weight: bold;
            color: #0a2540;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-subtitle {
            font-size: 9pt;
            color: #64748b;
            margin-top: 2px;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title-main {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
        }
        .doc-title-sub {
            font-size: 8.5pt;
            color: #64748b;
            font-family: monospace;
        }
        
        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #f1f5f9;
            padding: 6px 10px;
            border-left: 4px solid #2563eb;
            margin-top: 18px;
            margin-bottom: 10px;
        }

        .data-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1.5px solid #cbd5e1;
        }
        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5pt;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }

        .metric-box-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .metric-box {
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        }
        .metric-inflow {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }
        .metric-outflow {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .metric-netprofit {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }

        .metric-label {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .metric-value {
            font-size: 13pt;
            font-weight: bold;
            font-family: monospace;
        }

        .highlight-card {
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .signature-table {
            margin-top: 30px;
            width: 100%;
        }
        .signature-line {
            border-bottom: 1px solid #0f172a;
            width: 180px;
            margin-top: 55px;
            margin-bottom: 4px;
        }

        .footer-note {
            margin-top: 25px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            font-size: 7.5pt;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $companyName = $setting->company_name ?? 'CIO Network Solution';
    $companyTelp = $setting->telp ?? '6281234567890';
    $companyEmail = $setting->email ?? 'support@cionetworksolution.com';
    $companyAddress = $setting->address ?? 'Indonesia';

    $inflow = (float) ($dailyData['total_inflow'] ?? 0);
    $outflow = (float) ($dailyData['total_outflow'] ?? 0);
    $netProfit = (float) ($dailyData['net_profit'] ?? ($inflow - $outflow));
    $marginPct = (float) ($dailyData['profit_margin_pct'] ?? ($inflow > 0 ? ($netProfit / $inflow) * 100 : 0));

    $reportDate = !empty($dailyData['date']) 
        ? \Carbon\Carbon::parse($dailyData['date'])->translatedFormat('d F Y') 
        : \Carbon\Carbon::now()->translatedFormat('d F Y');
    
    $docRef = 'FIN-REP/' . (!empty($dailyData['date']) ? str_replace('-', '', $dailyData['date']) : date('Ymd')) . '/SH-' . str_pad((string)($shareholder->id ?? '1'), 3, '0', STR_PAD_LEFT);
@endphp

    <!-- Header Letterhead -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="company-title">{{ $companyName }}</div>
                <div class="company-subtitle">
                    {{ $companyAddress }}<br>
                    Email: {{ $companyEmail }} | Telp: {{ $companyTelp }}
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;" class="doc-title">
                <div class="doc-title-main">Laporan Finansial Harian</div>
                <div class="doc-title-sub">No. Dokumen: {{ $docRef }}</div>
                <div class="doc-title-sub">Tanggal Rekapan: {{ $reportDate }}</div>
            </td>
        </tr>
    </table>

    <!-- 1. Identitas Pemegang Saham -->
    <div class="section-title">1. Identitas Pemegang Saham (Investor)</div>
    <table class="data-table">
        <tr>
            <td style="width: 25%; color: #64748b;">Nama Pemegang Saham</td>
            <td style="width: 35%; font-weight: bold; color: #0f172a;">{{ $shareholder->name }}</td>
            <td style="width: 20%; color: #64748b;">Status Akun</td>
            <td style="width: 20%; font-weight: bold; color: #16a34a;">AKTIF (Verified)</td>
        </tr>
        <tr>
            <td style="color: #64748b;">Alamat Email</td>
            <td style="font-family: monospace;">{{ $shareholder->email }}</td>
            <td style="color: #64748b;">Kepemilikan Saham</td>
            <td style="font-weight: bold; color: #2563eb; font-family: monospace;">{{ number_format((float)$shareholder->total_percentage, 2) }}% ({{ number_format((int)$shareholder->total_shares, 0, ',', '.') }} Lembar)</td>
        </tr>
    </table>

    <!-- 2. Rincian Arus Transaksi per Unit Web & Gateway -->
    <div class="section-title">2. Rincian Arus Transaksi per Unit Web & Gateway</div>
    @if(!empty($dailyData['web_breakdown']))
        @foreach($dailyData['web_breakdown'] as $web)
            @php
                $wInflow = (float) ($web['total_inflow'] ?? 0);
                $wOutflow = (float) ($web['total_outflow'] ?? 0);
                $wNet = (float) ($web['net_profit'] ?? ($wInflow - $wOutflow));
                $inflowItems = $web['inflow_items'] ?? [];
                $outflowItems = $web['outflow_items'] ?? [];
            @endphp
            <div style="margin-bottom: 12px; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden;">
                <div style="background-color: #f1f5f9; padding: 6px 10px; font-size: 9.5pt; font-weight: bold; color: #0f172a; border-bottom: 1px solid #cbd5e1;">
                    <span style="color: #1e40af;">{{ $web['name'] }}</span> 
                    <span style="font-size: 8pt; color: #64748b; font-family: monospace;">({{ $web['code'] }})</span>
                    <span style="float: right; font-size: 8.5pt; color: #475569;">{{ $web['log_count'] ?? 0 }} Transaksi</span>
                </div>
                <table class="data-table" style="margin-bottom: 0;">
                    <thead>
                        <tr style="background-color: #ffffff;">
                            <th style="width: 15%; font-size: 8pt; padding: 5px 8px;">Tipe Arus</th>
                            <th style="width: 60%; font-size: 8pt; padding: 5px 8px;">Keperluan / Sumber Transaksi</th>
                            <th style="width: 25%; text-align: right; font-size: 8pt; padding: 5px 8px;">Nominal (IDR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($inflowItems))
                            @foreach(array_slice($inflowItems, 0, 6) as $item)
                                <tr>
                                    <td style="color: #15803d; font-weight: bold; font-size: 8.5pt; padding: 4px 8px;">🟢 Inflow</td>
                                    <td style="font-size: 8.5pt; padding: 4px 8px; color: #334155;">{{ $item['title'] }}</td>
                                    <td style="text-align: right; font-weight: bold; color: #15803d; font-family: monospace; font-size: 8.5pt; padding: 4px 8px;">
                                        +Rp {{ number_format((float)$item['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($inflowItems) > 6)
                                <tr>
                                    <td colspan="3" style="font-size: 8pt; color: #94a3b8; font-style: italic; padding: 3px 8px;">
                                        ...dan {{ count($inflowItems) - 6 }} transaksi masuk lainnya tercatat di sistem
                                    </td>
                                </tr>
                            @endif
                        @endif

                        @if(!empty($outflowItems))
                            @foreach(array_slice($outflowItems, 0, 6) as $item)
                                <tr>
                                    <td style="color: #b91c1c; font-weight: bold; font-size: 8.5pt; padding: 4px 8px;">🔴 Outflow</td>
                                    <td style="font-size: 8.5pt; padding: 4px 8px; color: #334155;">{{ $item['title'] }}</td>
                                    <td style="text-align: right; font-weight: bold; color: #b91c1c; font-family: monospace; font-size: 8.5pt; padding: 4px 8px;">
                                        -Rp {{ number_format((float)$item['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($outflowItems) > 6)
                                <tr>
                                    <td colspan="3" style="font-size: 8pt; color: #94a3b8; font-style: italic; padding: 3px 8px;">
                                        ...dan {{ count($outflowItems) - 6 }} transaksi keluar lainnya tercatat di sistem
                                    </td>
                                </tr>
                            @endif
                        @endif

                        @if(empty($inflowItems) && empty($outflowItems))
                            <tr>
                                <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic; font-size: 8.5pt; padding: 6px 8px;">
                                    Tidak ada mutasi transaksi pada unit web ini untuk tanggal laporan.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8fafc; font-weight: bold; border-top: 1.5px solid #cbd5e1;">
                            <td style="font-size: 8pt; padding: 5px 8px;">SUBTOTAL</td>
                            <td style="font-size: 8pt; padding: 5px 8px;">
                                Masuk: <span style="color: #15803d; font-family: monospace;">+Rp {{ number_format($wInflow, 0, ',', '.') }}</span> | 
                                Keluar: <span style="color: #b91c1c; font-family: monospace;">-Rp {{ number_format($wOutflow, 0, ',', '.') }}</span>
                            </td>
                            <td style="text-align: right; font-size: 8.5pt; color: {{ $wNet >= 0 ? '#1d4ed8' : '#b91c1c' }}; font-family: monospace; padding: 5px 8px;">
                                Net: Rp {{ number_format($wNet, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach
    @endif

    <!-- 3. Rekapitulasi Finansial Konsolidasi Harian (Paling Bawah) -->
    <div class="section-title">3. Total Finansial Konsolidasi Harian ({{ $reportDate }})</div>
    <table class="metric-box-table">
        <tr>
            <td style="width: 32%; padding-right: 6px;">
                <div class="metric-box metric-inflow">
                    <div class="metric-label">Total Uang Masuk</div>
                    <div class="metric-value">+Rp {{ number_format($inflow, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 32%; padding-left: 3px; padding-right: 3px;">
                <div class="metric-box metric-outflow">
                    <div class="metric-label">Total Uang Keluar</div>
                    <div class="metric-value">-Rp {{ number_format($outflow, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 36%; padding-left: 6px;">
                <div class="metric-box metric-netprofit">
                    <div class="metric-label">Laba Bersih Konsolidasi</div>
                    <div class="metric-value">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>Deskripsi Rekapitulasi</th>
                <th style="text-align: right;">Nilai Finansial</th>
                <th style="text-align: right;">Rasio / Catatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Seluruh Penerimaan Kas Konsolidasi (Inflow)</td>
                <td style="text-align: right; font-weight: bold; color: #15803d; font-family: monospace;">+Rp {{ number_format($inflow, 0, ',', '.') }}</td>
                <td style="text-align: right; color: #64748b;">100.0% Total Inflow</td>
            </tr>
            <tr>
                <td>Total Seluruh Pengeluaran Kas Konsolidasi (Outflow)</td>
                <td style="text-align: right; font-weight: bold; color: #b91c1c; font-family: monospace;">-Rp {{ number_format($outflow, 0, ',', '.') }}</td>
                <td style="text-align: right; color: #64748b;">{{ $inflow > 0 ? number_format(($outflow / $inflow) * 100, 1) : '0.0' }}% dari Inflow</td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td><strong>Keuntungan Bersih Konsolidasi Usaha (Net Profit)</strong></td>
                <td style="text-align: right; font-weight: bold; color: #1d4ed8; font-family: monospace; font-size: 11pt;">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold; color: #1d4ed8;">Margin: {{ number_format($marginPct, 1) }}%</td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan & Verifikasi Resmi -->
    <table class="signature-table">
        <tr>
            <td style="width: 60%; vertical-align: top; font-size: 8.5pt; color: #64748b;">
                <p style="margin: 0 0 4px 0;"><strong>Catatan Resmi Sistem:</strong></p>
                <p style="margin: 0 0 4px 0;">
                    &bull; Dokumen ini diterbitkan secara otomatis dan terverifikasi oleh Sistem Keuangan {{ $companyName }}.<br>
                    &bull; Nilai bagi hasil harian merupakan estimasi berbasis pergerakan kas *realtime* konsolidasi usaha.<br>
                    &bull; Pembayaran dividen aktual akan diproses sesuai periode transfer yang disetujui para pemegang saham.
                </p>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                <div style="font-size: 9pt; color: #475569;">Diterbitkan di Jakarta, {{ $reportDate }}</div>
                <div style="font-size: 9pt; font-weight: bold; color: #0f172a; margin-top: 2px;">Direksi & Tim Finansial</div>
                <div class="signature-line" style="margin-left: auto;"></div>
                <div style="font-size: 8.5pt; font-weight: bold; color: #1e40af;">{{ $companyName }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen Laporan Finansial Elektronik Resmi &bull; {{ $companyName }} &bull; Generated on {{ date('d/m/Y H:i:s') }} WIB
    </div>
</body>
</html>
