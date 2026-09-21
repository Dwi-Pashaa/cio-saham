<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Transfer Dividen Bagi Hasil</title>
    <style>
        /* Base Reset & Client Support */
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: 100%;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding-bottom: 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 620px;
            border-spacing: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .btn-invoice {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            display: inline-block;
            letter-spacing: 0.02em;
        }
        @media only screen and (max-width: 600px) {
            .content-padding {
                padding: 24px 20px !important;
            }
            .header-padding {
                padding: 24px 20px !important;
            }
            .stat-amount {
                font-size: 28px !important;
            }
        }
    </style>
</head>
<body style="background-color: #f1f5f9; margin: 0; padding: 0;">
@php
    $investorRecords = $investor->investors ?? collect([$investor->investor])->filter();
    $firstInv = $investorRecords->first();
    $accountNumber = $firstInv->party_1_account_number ?? '-';
    $companyName = $setting->company_name ?? 'CIO Network Solution';
    $companyTelp = $setting->telp ?? '6281234567890';
    $companyEmail = $setting->email ?? 'support@cionetworksolution.com';
    $code = $transfer->code ?: ('INV-TRF-' . $transfer->id);
    $netFormatted = 'Rp ' . number_format($transfer->amount, 0, ',', '.');
    $grossFormatted = 'Rp ' . number_format($transfer->gross_amount ?: $transfer->amount, 0, ',', '.');
    $adminFeeFormatted = 'Rp ' . number_format($transfer->admin_fee ?: 0, 0, ',', '.');
    $transferDate = \Carbon\Carbon::parse($transfer->transfer_date)->translatedFormat('d F Y');
    $notes = $transfer->notes ?: 'Distribusi bagi hasil dividen reguler';
@endphp

<center class="wrapper">
    <!-- Top Spacing -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td height="30" style="font-size: 30px; line-height: 30px;">&nbsp;</td>
        </tr>
    </table>

    <table class="main" width="100%" cellpadding="0" cellspacing="0" border="0">
        <!-- Top Accent Bar -->
        <tr>
            <td height="5" style="background: linear-gradient(90deg, #10b981 0%, #2563eb 100%); background-color: #2563eb; font-size: 5px; line-height: 5px;">&nbsp;</td>
        </tr>

        <!-- Header Section -->
        <tr>
            <td class="header-padding" style="padding: 32px 36px 24px 36px; background-color: #0f172a; text-align: left;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <div style="color: #38bdf8; font-size: 11px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 4px;">
                                INVESTOR PAYMENT RECEIPT
                            </div>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 0; line-height: 1.3;">
                                {{ $companyName }}
                            </h1>
                        </td>
                        <td align="right" valign="middle">
                            <span style="background-color: #064e3b; color: #34d399; border: 1px solid #059669; font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 999px; display: inline-block; letter-spacing: 0.05em;">
                                &#10003; BERHASIL
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Main Body -->
        <tr>
            <td class="content-padding" style="padding: 36px 36px 24px 36px; background-color: #ffffff;">
                <p style="font-size: 16px; color: #1e293b; margin: 0 0 16px 0; line-height: 1.5;">
                    Halo <strong>{{ $investor->name }}</strong>,
                </p>
                <p style="font-size: 14px; color: #64748b; margin: 0 0 28px 0; line-height: 1.6;">
                    Pembayaran distribusi bagi hasil dividen investasi Anda telah berhasil diproses dan dikirimkan ke rekening bank tujuan Anda.
                </p>

                <!-- Hero Amount Highlight Box -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf5 100%); background-color: #f0fdf4; border: 1.5px solid #86efac; border-radius: 14px; margin-bottom: 28px;">
                    <tr>
                        <td style="padding: 24px; text-align: center;">
                            <span style="color: #059669; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; display: block; margin-bottom: 6px;">
                                Total Bersih Diterima (Net Payout)
                            </span>
                            <div class="stat-amount" style="color: #065f46; font-size: 34px; font-weight: 800; margin: 0; line-height: 1.2; font-variant-numeric: tabular-nums;">
                                {{ $netFormatted }}
                            </div>
                            <span style="color: #64748b; font-size: 12px; margin-top: 6px; display: block;">
                                Ditransfer pada: <strong style="color: #334155;">{{ $transferDate }}</strong>
                            </span>
                        </td>
                    </tr>
                </table>

                <!-- Financial Details Table -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 28px; border-top: 1px solid #e2e8f0;">
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; color: #64748b;">
                            No. Referensi / Transaksi
                        </td>
                        <td align="right" style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; font-weight: 700; color: #1e40af; font-family: monospace;">
                            {{ $code }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; color: #64748b;">
                            Bank / Saluran Transfer
                        </td>
                        <td align="right" style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b;">
                            {{ $transfer->payment_method }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; color: #64748b;">
                            Nomor Rekening Tujuan
                        </td>
                        <td align="right" style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b; font-family: monospace;">
                            {{ $accountNumber }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; color: #64748b;">
                            Periode / Catatan
                        </td>
                        <td align="right" style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; font-weight: 600; color: #334155;">
                            {{ $notes }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; color: #64748b;">
                            Nominal Dividen Kotor (Gross)
                        </td>
                        <td align="right" style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b;">
                            {{ $grossFormatted }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; color: #64748b;">
                            Potongan Biaya Admin Sistem
                        </td>
                        <td align="right" style="padding: 12px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; font-weight: 700; color: #dc2626;">
                            - {{ $adminFeeFormatted }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 14px 0; border-bottom: 2px solid #0f172a; font-size: 14px; font-weight: 800; color: #0f172a;">
                            Total Bersih Ditransfer (Net)
                        </td>
                        <td align="right" style="padding: 14px 0; border-bottom: 2px solid #0f172a; font-size: 17px; font-weight: 800; color: #059669;">
                            {{ $netFormatted }}
                        </td>
                    </tr>
                </table>

                <!-- Action Button Call to Action -->
                @if(!empty($invoiceUrl))
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                    <tr>
                        <td align="center">
                            <a href="{{ $invoiceUrl }}" target="_blank" class="btn-invoice">
                                &#128196; Lihat Bukti Invoice & Rincian Transfer
                            </a>
                        </td>
                    </tr>
                </table>
                @endif

                <p style="font-size: 12px; color: #94a3b8; text-align: center; margin: 0; line-height: 1.5;">
                    Jika Anda memiliki pertanyaan mengenai distribusi dividen ini, silakan hubungi tim Finance melalui kontak di bawah ini.
                </p>
            </td>
        </tr>

        <!-- Footer Section -->
        <tr>
            <td style="padding: 24px 36px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                <p style="font-size: 12px; color: #64748b; margin: 0 0 8px 0; font-weight: 600;">
                    {{ $companyName }} &bull; Investor Management Portal
                </p>
                <p style="font-size: 11px; color: #94a3b8; margin: 0 0 12px 0;">
                    Email: <a href="mailto:{{ $companyEmail }}" style="color: #2563eb; text-decoration: none;">{{ $companyEmail }}</a> &bull; Telp / WhatsApp: <a href="https://wa.me/{{ preg_replace('/\D/', '', $companyTelp) }}" style="color: #2563eb; text-decoration: none;">{{ $companyTelp }}</a>
                </p>
                <p style="font-size: 10px; color: #cbd5e1; margin: 0;">
                    Email ini dikirimkan secara otomatis oleh sistem notifikasi dividen. Harap tidak membalas langsung ke alamat email ini jika tidak dimonitor.
                </p>
            </td>
        </tr>
    </table>

    <!-- Bottom Spacing -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td height="40" style="font-size: 40px; line-height: 40px;">&nbsp;</td>
        </tr>
    </table>
</center>
</body>
</html>
