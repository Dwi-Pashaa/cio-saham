@props(['logs', 'webFilter' => null])

@php
    $logsCollection = is_iterable($logs) ? collect($logs) : collect([]);

    // Helper untuk normalisasi item log
    $normalizeItem = function($log) {
        $arr = is_array($log) ? $log : (is_object($log) && method_exists($log, 'toArray') ? $log->toArray() : (array) $log);
        $rawCode = strtoupper($arr['source_client_code'] ?? 'CIO_FINANCE');
        $eventRaw = strtolower($arr['event'] ?? 'created');
        $subjectType = $arr['subject_type'] ?? 'General';
        $props = is_array($arr['raw_payload']['properties'] ?? null) 
            ? $arr['raw_payload']['properties'] 
            : (is_array($arr['properties'] ?? null) ? $arr['properties'] : []);
        $rawAmount = (float) ($arr['amount'] ?? 0);
        $createdAtRaw = $arr['log_created_at'] ?? $arr['created_at'] ?? now();
        $createdAt = \Carbon\Carbon::parse($createdAtRaw);

        $isXendit = ($rawCode === 'XENDIT' || str_contains($rawCode, 'XENDIT'));

        // Normalisasi code
        $code = match($rawCode) {
            'WEB_SLIP_GAJI'   => 'WEB_SLIP_GAJI',
            'CIO_OPERASIONAL' => 'CIO_OPERASIONAL',
            'CIO_INVESTOR'    => 'CIO_INVESTOR',
            'XENDIT'          => 'XENDIT',
            default           => 'CIO_FINANCE',
        };

        // Ambil nominal
        $amount = $rawAmount;
        if ($amount == 0) {
            if (!empty($props['attributes']['amount'])) {
                $amount = (float) $props['attributes']['amount'];
            } elseif (!empty($props['old']['amount'])) {
                $amount = (float) $props['old']['amount'];
            }
        }

        // Ambil reference ID
        $refId = $props['reference_id'] ?? $arr['subject_external_id'] ?? (!empty($props['attributes']['finance_category_id']) ? $props['attributes']['finance_category_id'] : ($arr['id'] ?? '-'));

        if ($isXendit) {
            $eventBadge = 'INVOICE_PAID';
            $eventBadgeClass = 'badge-deduct';
            $borderClass = 'border-card-blue';
            $iconType = 'clock';
            $actor = 'Xendit Gateway';
            $clientLabel = 'Xendit Gateway';
            $isWebsiteClient = true;
            $module = 'Modul: Payment Gateway';
            $title = !empty($arr['description']) && $arr['description'] !== 'created' 
                ? $arr['description'] 
                : 'Pembayaran sebesar Rp ' . number_format($amount, 0, ',', '.') . ' via ' . ($props['channel'] ?? 'QRIS');
        } elseif ($code === 'WEB_SLIP_GAJI') {
            $eventBadge = strtoupper($eventRaw); // DEDUCT_BALANCE
            $eventBadgeClass = 'badge-deduct';
            $borderClass = 'border-card-blue';
            $iconType = 'clock';
            $actor = 'Sistem / API';
            $clientLabel = 'Web Slip Gaji';
            $isWebsiteClient = true;
            $module = 'Modul: Pengeluaran';
            $title = 'Administrator pada Web Slip Gaji ' . ($arr['description'] ?? '');
            if ($amount > 0 && !str_contains($title, 'sebesar')) {
                $title .= ' sebesar Rp ' . number_format($amount, 0, ',', '.');
            }
        } elseif ($code === 'CIO_OPERASIONAL') {
            $eventBadge = strtoupper($eventRaw);
            $eventBadgeClass = 'badge-ubah';
            $borderClass = 'border-card-yellow';
            $iconType = 'edit';
            $actor = 'Sistem / Operasional';
            $clientLabel = 'CIO Operasional';
            $isWebsiteClient = true;
            $module = 'Modul: Operasional';
            $title = !empty($arr['description']) && $arr['description'] !== 'updated' ? $arr['description'] : 'Aktivitas pada sistem CIO Operasional';
        } elseif ($code === 'CIO_INVESTOR') {
            $eventBadge = strtoupper($eventRaw);
            $eventBadgeClass = 'badge-tambah';
            $borderClass = 'border-card-green';
            $iconType = 'plus';
            $actor = 'Administrator';
            $clientLabel = 'CIO Investor Portal';
            $isWebsiteClient = true;
            $module = 'Modul: Investor';
            $title = !empty($arr['description']) && $arr['description'] !== 'created' ? $arr['description'] : 'Aktivitas pada CIO Investor Portal';
        } else {
            // CIO_FINANCE
            $isWebsiteClient = false;
            $clientLabel = 'CIO Finance';
            $actor = 'Administrator';

            if ($eventRaw === 'created') {
                $eventBadge = 'TAMBAH';
                $eventBadgeClass = 'badge-tambah';
                $borderClass = 'border-card-green';
                $iconType = 'plus';

                if (strtolower($subjectType) === 'expense' || ($props['attributes']['type'] ?? '') === 'expense') {
                    $module = 'Modul: Pengeluaran';
                    $name = $props['attributes']['name'] ?? $props['attributes']['payee'] ?? $props['attributes']['description'] ?? 'Lainnya';
                    $title = "Administrator menambahkan pengeluaran" . ($amount > 0 ? " sebesar Rp " . number_format($amount, 0, ',', '.') : "") . " untuk " . $name;
                } elseif (strtolower($subjectType) === 'income' || ($props['attributes']['type'] ?? '') === 'income') {
                    $module = 'Modul: Pemasukan';
                    $name = $props['attributes']['name'] ?? $props['attributes']['source'] ?? 'Lainnya';
                    $title = "Administrator menambahkan pemasukan" . ($amount > 0 ? " sebesar Rp " . number_format($amount, 0, ',', '.') : "") . " untuk " . $name;
                } else {
                    $module = 'Modul: Kategori Finansial';
                    $name = $props['attributes']['name'] ?? 'Kategori Baru';
                    $title = "Administrator menambahkan kategori " . $name;
                }
            } elseif ($eventRaw === 'updated') {
                $eventBadge = 'UBAH';
                $eventBadgeClass = 'badge-ubah';
                $borderClass = 'border-card-yellow';
                $iconType = 'edit';
                $module = 'Modul: Pengeluaran';
                $name = $props['attributes']['name'] ?? $props['attributes']['description'] ?? $props['old']['description'] ?? 'Lainnya';
                $title = "Administrator memperbarui pengeluaran" . ($amount > 0 ? " sebesar Rp " . number_format($amount, 0, ',', '.') : "") . " untuk " . $name;
            } elseif ($eventRaw === 'deleted') {
                $eventBadge = 'HAPUS';
                $eventBadgeClass = 'badge-hapus';
                $borderClass = 'border-card-red';
                $iconType = 'trash';
                $module = 'Modul: Pengeluaran';
                $name = $props['old']['source'] ?? $props['old']['payee'] ?? $props['old']['description'] ?? 'Lainnya';
                $title = "Administrator menghapus transaksi" . ($amount > 0 ? " sebesar Rp " . number_format($amount, 0, ',', '.') : "") . " : " . $name;
            } else {
                $eventBadge = strtoupper($eventRaw);
                $eventBadgeClass = 'badge-deduct';
                $borderClass = 'border-card-blue';
                $iconType = 'clock';
                $module = 'Modul: Keuangan';
                $title = "Administrator melakukan {$eventRaw} transaksi";
            }
        }

        return [
            'id'                => $arr['id'] ?? null,
            'title'             => $title,
            'event_badge'       => $eventBadge,
            'event_badge_class' => $eventBadgeClass,
            'border_class'      => $borderClass,
            'icon_type'         => $iconType,
            'actor'             => $actor,
            'client_label'      => $clientLabel,
            'client_code'       => $code,
            'is_website_client' => $isWebsiteClient,
            'module'            => $module,
            'ref_id'            => $refId,
            'amount'            => $amount,
            'is_xendit'         => $isXendit,
            'created_at'        => $createdAt,
        ];
    };

    $allNormalized = $logsCollection->map($normalizeItem);

    // Tab 1: Log Internal (Non-Xendit transactions)
    $internalLogs = $allNormalized->filter(fn($item) => !$item['is_xendit'])->values();

    // Daftar Lengkap Unit Web Internal
    $internalWebClientsList = [
        'ALL'             => 'Semua Unit Web',
        'WEB_SLIP_GAJI'   => 'Web Slip Gaji',
        'CIO_FINANCE'     => 'CIO Finance',
        'CIO_OPERASIONAL' => 'CIO Operasional',
        'CIO_INVESTOR'    => 'CIO Investor Portal',
    ];

    // Tab 2: Transaksi Xendit (Gateway transactions)
    $xenditLogs = $allNormalized->filter(fn($item) => $item['is_xendit'])->values();
@endphp

<style>
    .finance-logs-wrapper {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }
    .finance-logs-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
    }
    .finance-logs-nav {
        display: inline-flex;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 10px;
        gap: 6px;
        border: 1px solid #e2e8f0;
    }
    .finance-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        white-space: nowrap;
    }
    .finance-tab-btn.active {
        background-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }
    .finance-tab-btn .tab-count-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-family: monospace;
        font-weight: 700;
        background: #e2e8f0;
        color: #334155;
    }
    .finance-tab-btn.active .tab-count-pill {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* Sub-Filter Dropdown */
    .internal-web-dropdown-btn {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        transition: all 0.18s ease;
    }
    .internal-web-dropdown-btn:hover, .internal-web-dropdown-btn:focus {
        background: #f8fafc;
        border-color: #0284c7;
        color: #0284c7;
    }
    .internal-filter-dropdown-menu .dropdown-item {
        font-size: 0.85rem;
        padding: 8px 16px;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
    }
    .internal-filter-dropdown-menu .dropdown-item:hover {
        background-color: #f1f5f9;
        color: #0284c7;
    }
    .internal-filter-dropdown-menu .dropdown-item.active {
        background-color: #eff6ff;
        color: #1d4ed8;
        font-weight: 700;
    }

    /* Cards */
    .log-item-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-left-width: 4px !important;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.2s ease;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
    }
    .log-item-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.06);
    }
    .border-card-blue { border-left-color: #3b82f6 !important; }
    .border-card-yellow { border-left-color: #f59e0b !important; }
    .border-card-green { border-left-color: #10b981 !important; }
    .border-card-red { border-left-color: #ef4444 !important; }

    .log-card-header-mobile {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        flex: 1;
        min-width: 0;
    }

    .log-card-right-section {
        flex-shrink: 0;
        text-align: right;
        min-width: 170px;
    }

    .log-card-title-text {
        font-size: 0.95rem;
        line-height: 1.5;
        font-weight: 600;
        color: #1e293b;
    }

    .log-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .icon-blue { background-color: #eff6ff; color: #2563eb; }
    .icon-yellow { background-color: #fffbeb; color: #d97706; }
    .icon-green { background-color: #ecfdf5; color: #059669; }
    .icon-red { background-color: #fef2f2; color: #dc2626; }

    .badge-deduct { background-color: #e0f2fe; color: #0284c7; font-weight: 700; }
    .badge-ubah { background-color: #fef3c7; color: #b45309; font-weight: 700; }
    .badge-tambah { background-color: #dcfce7; color: #15803d; font-weight: 700; }
    .badge-hapus { background-color: #fee2e2; color: #b91c1c; font-weight: 700; }
    .badge-src-api { background-color: #0284c7; color: #ffffff; font-weight: 600; }
    .badge-src-internal { background-color: #475569; color: #ffffff; font-weight: 600; }
    .badge-modul { background-color: #f8fafc; color: #475569; border: 1px solid #cbd5e1; font-weight: 500; }

    .log-meta-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 6px;
        padding: 3px 8px;
        font-size: 0.75rem;
        color: #475569;
        white-space: nowrap;
    }
    .log-ref-mono {
        font-family: monospace;
        color: #0284c7;
        font-weight: 600;
    }

    .log-amount-text {
        font-weight: 800;
        color: #2563eb;
        font-size: 1.15rem;
        font-family: monospace;
        letter-spacing: -0.3px;
        white-space: nowrap;
    }

    /* Mobile Fine-Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .finance-logs-wrapper {
            padding: 14px 10px;
            border-radius: 14px;
        }
        .finance-logs-header {
            gap: 12px;
            margin-bottom: 16px;
        }
        .finance-logs-nav {
            width: 100%;
        }
        .finance-tab-btn {
            flex: 1;
            justify-content: center;
            padding: 8px 6px;
            font-size: 0.8rem;
            gap: 6px;
        }
        .log-item-card {
            flex-direction: column;
            gap: 10px;
            padding: 14px 12px;
            margin-bottom: 10px;
        }
        .log-card-header-mobile {
            gap: 10px;
            width: 100%;
        }
        .log-card-title-text {
            font-size: 0.88rem !important;
            line-height: 1.4 !important;
            font-weight: 600 !important;
        }
        .log-card-right-section {
            width: 100%;
            border-top: 1px dashed #e2e8f0;
            padding-top: 10px;
            margin-top: 4px;
            margin-left: 0 !important;
            text-align: left;
            min-width: unset;
        }
        .log-mobile-footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            gap: 8px;
        }
        .log-mobile-date-row {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #64748b;
            font-size: 0.74rem;
            font-family: monospace;
            margin-top: 4px;
        }
    }
</style>

<div class="finance-logs-wrapper mt-4">
    <!-- Header Section -->
    <div class="finance-logs-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="text-primary d-inline-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 15"></polyline></svg>
                </span>
                <h3 class="fw-bold text-dark mb-0 fs-2" style="letter-spacing: -0.3px;">Log Aktivitas & Transaksi</h3>
            </div>
            <p class="text-muted small mb-0">
                Pantau aktivitas internal sistem dan riwayat transaksi gateway Xendit
            </p>
        </div>

        <!-- 2 Tabs Toggle (Log Internal vs Transaksi Xendit) -->
        <div class="finance-logs-nav" role="tablist">
            <button class="finance-tab-btn active" id="tab-btn-internal" data-bs-toggle="tab" data-bs-target="#tab-pane-internal" type="button" role="tab" aria-selected="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="M14 9h4"/><path d="M14 15h4"/></svg>
                <span>Log Internal</span>
                <span class="tab-count-pill" id="tab-internal-total-count">{{ count($internalLogs) }}</span>
            </button>
            <button class="finance-tab-btn" id="tab-btn-xendit" data-bs-toggle="tab" data-bs-target="#tab-pane-xendit" type="button" role="tab" aria-selected="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                <span>Transaksi Xendit</span>
                <span class="tab-count-pill">{{ count($xenditLogs) }}</span>
            </button>
        </div>
    </div>

    <!-- Tab Skeleton Shimmer Loading Placeholder -->
    <div id="tab-logs-skeleton" class="d-none">
        <div class="d-flex flex-column gap-2">
            @for($i = 0; $i < 3; $i++)
                <div class="skeleton-card">
                    <div class="d-flex align-items-start gap-2.5">
                        <div class="skeleton-shimmer skeleton-circle" style="width: 38px; height: 38px;"></div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="skeleton-shimmer skeleton-text" style="width: 85%; height: 16px;"></div>
                            <div class="skeleton-shimmer skeleton-text" style="width: 55%; height: 14px;"></div>
                            <div class="d-flex flex-wrap gap-1.5 mt-2">
                                <div class="skeleton-shimmer skeleton-pill" style="width: 90px; height: 20px;"></div>
                                <div class="skeleton-shimmer skeleton-pill" style="width: 115px; height: 20px;"></div>
                                <div class="skeleton-shimmer skeleton-pill" style="width: 85px; height: 20px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content" style="transition: opacity 0.2s ease;">
        <!-- ==========================================
             TAB 1: LOG INTERNAL
             ========================================== -->
        <div class="tab-pane fade show active" id="tab-pane-internal" role="tabpanel">
            @if($internalLogs->isEmpty())
                <div class="text-center py-5">
                    <div class="text-muted mb-2 opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <h5 class="fw-bold text-dark">Belum Ada Log Internal</h5>
                    <p class="text-muted small mb-0">Aktivitas Slip Gaji dan perubahan data internal akan muncul di sini.</p>
                </div>
            @else
                <!-- Filter Select (Dropdown Only) -->
                <div class="d-flex align-items-center mb-3">
                    <div class="dropdown">
                        <button class="btn internal-web-dropdown-btn dropdown-toggle d-flex align-items-center gap-2" type="button" id="internalWebFilterDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-filter text-muted" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" /></svg>
                            <span id="selected-internal-web-label">Semua Unit Web</span>
                        </button>
                        <div class="dropdown-menu internal-filter-dropdown-menu shadow-sm border py-1" id="internal-web-filter-menu" style="border-radius: 10px; min-width: 190px;">
                            @foreach($internalWebClientsList as $codeKey => $unitLabel)
                                <button type="button" class="dropdown-item internal-filter-option py-2 {{ $codeKey === 'ALL' ? 'active' : '' }}" data-client-code="{{ $codeKey }}" data-client-name="{{ $unitLabel }}">
                                    {{ $unitLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Empty State after Filtering -->
                <div id="internal-filter-empty" class="text-center py-5 d-none">
                    <div class="text-muted mb-2 opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Tidak Ada Log Sesuai Filter</h6>
                    <p class="text-muted small mb-0">Tidak ditemukan catatan aktivitas internal yang cocok dengan unit web yang dipilih.</p>
                </div>

                <!-- Internal Log Cards List -->
                <div class="d-flex flex-column" id="internal-logs-list">
                    @foreach($internalLogs as $item)
                        @php
                            $iconColorClass = match($item['icon_type']) {
                                'clock' => 'icon-blue',
                                'edit'  => 'icon-yellow',
                                'plus'  => 'icon-green',
                                'trash' => 'icon-red',
                                default => 'icon-blue',
                            };
                        @endphp
                        <div class="log-item-card internal-log-card {{ $item['border_class'] }}"
                             data-client-label="{{ strtolower($item['client_label']) }}"
                             data-client-code="{{ strtoupper($item['client_code']) }}">
                            <!-- Top / Left: Icon + Title Group -->
                            <div class="log-card-header-mobile">
                                <!-- Icon Circle -->
                                <div class="log-icon-box {{ $iconColorClass }}">
                                    @if($item['icon_type'] === 'clock')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
                                    @elseif($item['icon_type'] === 'edit')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    @elseif($item['icon_type'] === 'plus')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    @elseif($item['icon_type'] === 'trash')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>
                                    @endif
                                </div>

                                <!-- Title and Badges & Meta Container -->
                                <div class="flex-grow-1 min-w-0">
                                    <!-- Title / Description -->
                                    <div class="text-dark fw-bold mb-2 fs-4 log-card-title-text" style="line-height: 1.45; word-break: break-word;">
                                        {{ $item['title'] }}
                                    </div>

                                    <!-- Badges Row -->
                                    <div class="d-flex align-items-center flex-wrap gap-1.5 mb-2">
                                        <span class="badge {{ $item['event_badge_class'] }} rounded-pill px-2.5 py-1">
                                            {{ $item['event_badge'] }}
                                        </span>

                                        @if($item['client_code'] === 'CIO_FINANCE')
                                            <span class="badge badge-src-internal rounded-pill px-2.5 py-1">
                                                CIO Finance
                                            </span>
                                        @elseif($item['is_website_client'])
                                            <span class="badge badge-src-api rounded-pill px-2.5 py-1">
                                                {{ $item['client_label'] }} (API)
                                            </span>
                                        @else
                                            <span class="badge badge-src-internal rounded-pill px-2.5 py-1">
                                                {{ $item['client_label'] }}
                                            </span>
                                        @endif

                                        <span class="badge badge-modul rounded-pill px-2.5 py-1">
                                            {{ $item['module'] }}
                                        </span>
                                    </div>

                                    <!-- Metadata Tags Row -->
                                    <div class="d-flex align-items-center flex-wrap gap-1.5 text-muted small">
                                        <span class="log-meta-tag">
                                            <span>👤 Pelaku:</span>
                                            <strong class="text-dark">{{ $item['actor'] }}</strong>
                                        </span>

                                        <span class="log-meta-tag">
                                            <span>💻 Unit Web:</span>
                                            <strong class="text-dark">{{ $item['client_label'] }}</strong>
                                        </span>

                                        @if(!empty($item['ref_id']) && $item['ref_id'] !== '-')
                                            <span class="log-meta-tag">
                                                <span>≡ Ref ID:</span>
                                                <span class="log-ref-mono">{{ $item['ref_id'] }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right Section / Mobile Bottom Section: Nominal & Timestamps -->
                            <div class="log-card-right-section text-md-end flex-shrink-0 ms-md-3">
                                <!-- Desktop View Layout (>= 768px) -->
                                <div class="d-none d-md-block">
                                    @if($item['amount'] > 0)
                                        <div class="log-amount-text mb-1">
                                            Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                        </div>
                                    @endif

                                    <div class="text-muted small font-monospace d-flex align-items-center justify-content-end gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span style="white-space: nowrap;">{{ $item['created_at']->translatedFormat('d M Y, H:i:s') }}</span>
                                    </div>
                                    <div class="text-muted small mt-0.5" style="font-size: 0.76rem; white-space: nowrap;">
                                        {{ $item['created_at']->diffForHumans() }}
                                    </div>
                                </div>

                                <!-- Mobile View Layout (< 768px) -->
                                <div class="d-md-none w-100">
                                    <div class="log-mobile-footer-row">
                                        @if($item['amount'] > 0)
                                            <div class="log-amount-text">
                                                Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                            </div>
                                        @else
                                            <span class="badge bg-secondary-lt font-monospace">Non-Moneter</span>
                                        @endif

                                        <span class="badge bg-light text-muted font-monospace" style="font-size: 0.72rem; border: 1px solid #e2e8f0;">
                                            {{ $item['created_at']->diffForHumans() }}
                                        </span>
                                    </div>

                                    <div class="log-mobile-date-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span>{{ $item['created_at']->translatedFormat('d M Y, H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- ==========================================
             TAB 2: TRANSAKSI XENDIT
             ========================================== -->
        <div class="tab-pane fade" id="tab-pane-xendit" role="tabpanel">
            @if($xenditLogs->isEmpty())
                <div class="text-center py-5">
                    <div class="text-muted mb-2 opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                    <h5 class="fw-bold text-dark">Belum Ada Riwayat Transaksi Xendit</h5>
                    <p class="text-muted small mb-0">Pembayaran QRIS dan Virtual Account pelanggan akan otomatis tercatat di sini.</p>
                </div>
            @else
                <div class="d-flex flex-column">
                    @foreach($xenditLogs as $item)
                        <div class="log-item-card border-card-blue">
                            <!-- Top / Left: Icon + Title Group -->
                            <div class="log-card-header-mobile">
                                <!-- Icon Circle -->
                                <div class="log-icon-box icon-blue">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
                                </div>

                                <!-- Middle Section -->
                                <div class="flex-grow-1 min-w-0">
                                    <div class="text-dark fw-bold mb-2 fs-4 log-card-title-text" style="line-height: 1.45; word-break: break-word;">
                                        {{ $item['title'] }}
                                    </div>

                                    <div class="d-flex align-items-center flex-wrap gap-1.5 mb-2">
                                        <span class="badge badge-deduct rounded-pill px-2.5 py-1">
                                            {{ $item['event_badge'] }}
                                        </span>
                                        <span class="badge badge-src-api rounded-pill px-2.5 py-1">
                                            Gateway Xendit
                                        </span>
                                        <span class="badge badge-modul rounded-pill px-2.5 py-1">
                                            Modul: Payment Gateway
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center flex-wrap gap-1.5 text-muted small">
                                        <span class="log-meta-tag">
                                            <span>👤 Pelaku:</span>
                                            <strong class="text-dark">Xendit Gateway</strong>
                                        </span>

                                        @if(!empty($item['ref_id']) && $item['ref_id'] !== '-')
                                            <span class="log-meta-tag">
                                                <span>≡ Ref ID:</span>
                                                <span class="log-ref-mono">{{ $item['ref_id'] }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right Section / Mobile Bottom Section -->
                            <div class="log-card-right-section text-md-end flex-shrink-0 ms-md-3">
                                <!-- Desktop View Layout (>= 768px) -->
                                <div class="d-none d-md-block">
                                    @if($item['amount'] > 0)
                                        <div class="log-amount-text mb-1">
                                            Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                        </div>
                                    @endif

                                    <div class="text-muted small font-monospace d-flex align-items-center justify-content-end gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span style="white-space: nowrap;">{{ $item['created_at']->translatedFormat('d M Y, H:i:s') }}</span>
                                    </div>
                                    <div class="text-muted small mt-0.5" style="font-size: 0.76rem; white-space: nowrap;">
                                        {{ $item['created_at']->diffForHumans() }}
                                    </div>
                                </div>

                                <!-- Mobile View Layout (< 768px) -->
                                <div class="d-md-none w-100">
                                    <div class="log-mobile-footer-row">
                                        @if($item['amount'] > 0)
                                            <div class="log-amount-text">
                                                Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                            </div>
                                        @else
                                            <span class="badge bg-secondary-lt font-monospace">Non-Moneter</span>
                                        @endif

                                        <span class="badge bg-light text-muted font-monospace" style="font-size: 0.72rem; border: 1px solid #e2e8f0;">
                                            {{ $item['created_at']->diffForHumans() }}
                                        </span>
                                    </div>

                                    <div class="log-mobile-date-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span>{{ $item['created_at']->translatedFormat('d M Y, H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tab switching animation
    const tabBtns = document.querySelectorAll('.finance-tab-btn');
    const skeletonEl = document.getElementById('tab-logs-skeleton');
    const tabContentEl = document.querySelector('.finance-logs-wrapper .tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('show.bs.tab', function() {
            if (skeletonEl && tabContentEl) {
                skeletonEl.classList.remove('d-none');
                tabContentEl.style.opacity = '0';
                
                setTimeout(() => {
                    skeletonEl.classList.add('d-none');
                    tabContentEl.style.opacity = '1';
                }, 160);
            }
        });
    });

    // 2. Sub-Filter Web Internal Logic (Dropdown Only)
    const dropdownOptions = document.querySelectorAll('.internal-filter-option');
    const logCards = document.querySelectorAll('.internal-log-card');
    const emptyFilterEl = document.getElementById('internal-filter-empty');
    const selectedLabelEl = document.getElementById('selected-internal-web-label');

    let activeCode = 'ALL';

    function applyInternalFilters() {
        let visibleCount = 0;

        logCards.forEach(card => {
            const cardCode = card.getAttribute('data-client-code') || '';
            const matchesClient = (activeCode === 'ALL') || (cardCode === activeCode);

            if (matchesClient) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (emptyFilterEl) {
            if (visibleCount === 0 && logCards.length > 0) {
                emptyFilterEl.classList.remove('d-none');
            } else {
                emptyFilterEl.classList.add('d-none');
            }
        }
    }

    function setActiveFilter(code, name) {
        activeCode = code;

        // Update dropdown menu items active class
        dropdownOptions.forEach(opt => {
            if (opt.getAttribute('data-client-code') === code) {
                opt.classList.add('active');
            } else {
                opt.classList.remove('active');
            }
        });

        // Update dropdown toggle label
        if (selectedLabelEl) {
            selectedLabelEl.textContent = name;
        }

        applyInternalFilters();
    }

    // Dropdown clicks
    dropdownOptions.forEach(opt => {
        opt.addEventListener('click', function(e) {
            e.preventDefault();
            const code = this.getAttribute('data-client-code') || 'ALL';
            const name = this.getAttribute('data-client-name') || 'Semua Unit Web';
            setActiveFilter(code, name);
        });
    });
});
</script>
@endpush
