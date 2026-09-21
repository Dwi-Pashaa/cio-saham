<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Shareholder;
use App\Services\ShareholderService;
use Illuminate\Http\Request;

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

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('activeHoldings', function ($hq) use ($search) {
                      $hq->where('share_code', 'like', "%{$search}%")
                         ->orWhere('entity_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shareholders = $query->paginate(10)->withQueryString();
        $equitySummary = $this->shareholderService->getEquitySummary();

        return view('pages.investor-directory.index', compact('shareholders', 'equitySummary'));
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
