<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Shareholder;
use App\Services\ShareholderService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InvestorDirectoryController extends Controller
{
    protected ShareholderService $shareholderService;

    public function __construct(ShareholderService $shareholderService)
    {
        $this->shareholderService = $shareholderService;
    }

    /**
     * Tampilkan direktori portofolio seluruh pemegang saham (Read-Only & Search).
     */
    public function index(Request $request)
    {
        $query = Shareholder::with(['activeHoldings'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $equitySummary = $this->shareholderService->getEquitySummary();

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->filter(function ($q) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = trim($request->input('search.value'));
                        $q->where(function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhereHas('activeHoldings', function ($hq) use ($search) {
                                    $hq->where('share_code', 'like', "%{$search}%")
                                       ->orWhere('entity_name', 'like', "%{$search}%");
                                });
                        });
                    }
                    if ($request->filled('status')) {
                        $q->where('status', $request->status);
                    }
                })
                ->addColumn('shareholder_info', function ($sh) {
                    $initials = strtoupper(substr($sh->name, 0, 2));
                    $name = e($sh->name);
                    $email = $sh->email ? '<span class="text-muted small d-block text-truncate" style="font-size: 0.775rem;">' . e($sh->email) . '</span>' : '';
                    $showUrl = route('investor-directory.show', $sh->id);

                    return '<div class="d-flex align-items-center gap-2.5">
                                <div class="shareholder-avatar-circle" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                    ' . $initials . '
                                </div>
                                <div class="overflow-hidden">
                                    <a href="' . $showUrl . '" class="text-dark fw-bold d-block text-decoration-none hover-primary text-truncate" title="' . $name . '">
                                        ' . $name . '
                                    </a>
                                    ' . $email . '
                                </div>
                            </div>';
                })
                ->addColumn('holdings_badge', function ($sh) {
                    $count = $sh->activeHoldings->count();
                    return '<div class="text-center">
                                <span class="badge bg-purple-lt fw-bold d-inline-flex align-items-center gap-1 font-monospace px-2.5 py-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /></svg>
                                    <span>' . $count . ' Saham</span>
                                </span>
                            </div>';
                })
                ->addColumn('total_shares_formatted', function ($sh) {
                    return '<div class="text-end fw-bold font-monospace" style="white-space: nowrap !important;">
                                <span class="text-dark fs-4">' . number_format($sh->total_shares, 0, ',', '.') . '</span>
                                <span class="small text-muted fw-normal">Lembar</span>
                            </div>';
                })
                ->addColumn('total_investment_formatted', function ($sh) {
                    return '<div class="text-end fw-bold font-monospace" style="white-space: nowrap !important;">
                                <span class="text-primary fs-4">Rp ' . number_format($sh->total_investment, 0, ',', '.') . '</span>
                            </div>';
                })
                ->addColumn('total_percentage_formatted', function ($sh) {
                    return '<div class="text-center">
                                <span class="badge bg-success-lt fw-bold font-monospace px-2.5 py-1" style="font-size: 0.85rem;">
                                    ' . number_format($sh->total_percentage, 2) . '%
                                </span>
                            </div>';
                })
                ->addColumn('status_badge', function ($sh) {
                    if ($sh->status === 'active') {
                        return '<div class="text-center">
                                    <span class="badge bg-success-lt text-success fw-bold d-inline-flex align-items-center gap-1 px-2 py-1">
                                        <span class="pulse-live-dot" style="width: 6px; height: 6px;"></span>
                                        <span>Aktif</span>
                                    </span>
                                </div>';
                    }
                    return '<div class="text-center">
                                <span class="badge bg-danger-lt text-danger fw-bold px-2 py-1">
                                    Non-Aktif
                                </span>
                            </div>';
                })
                ->addColumn('action', function ($sh) {
                    return '<div class="text-end">
                                <a href="' . route('investor-directory.show', $sh->id) . '" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5 shadow-sm" title="Lihat Portofolio Saham">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                    <span>Lihat Portofolio</span>
                                </a>
                            </div>';
                })
                ->rawColumns(['shareholder_info', 'holdings_badge', 'total_shares_formatted', 'total_investment_formatted', 'total_percentage_formatted', 'status_badge', 'action'])
                ->make(true);
        }

        return view('pages.investor-directory.index', compact('equitySummary'));
    }

    /**
     * Tampilkan rincian detail portofolio 1 pemegang saham (Read-Only).
     */
    public function show($id)
    {
        $shareholder = Shareholder::with(['holdings' => function ($q) {
            $q->orderBy('id', 'asc');
        }, 'user'])->findOrFail($id);

        $equitySummary = $this->shareholderService->getEquitySummary();

        return view('pages.investor-directory.show', compact('shareholder', 'equitySummary'));
    }
}

