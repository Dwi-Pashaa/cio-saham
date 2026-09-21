<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Shareholder;
use App\Models\User;
use App\Services\FinanceAnalyticsService;
use App\Services\ShareholderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    protected FinanceAnalyticsService $analyticsService;
    protected ShareholderService $shareholderService;

    public function __construct(
        FinanceAnalyticsService $analyticsService,
        ShareholderService $shareholderService
    ) {
        $this->analyticsService   = $analyticsService;
        $this->shareholderService = $shareholderService;
    }

    /**
     * Display the user's permanent profile view (Web & Mobile responsive).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Cari data pemegang saham jika akun terhubung sebagai investor
        $shareholder = Shareholder::with(['holdings' => function ($q) {
            $q->where('status', 'active')->orderBy('id', 'asc');
        }])
        ->where('user_id', $user->id)
        ->orWhere('email', $user->email)
        ->first();

        if (!$shareholder) {
            $shareholder = Shareholder::with(['holdings' => function ($q) {
                $q->where('status', 'active')->orderBy('id', 'asc');
            }])
            ->where('name', 'like', '%' . $user->name . '%')
            ->first();
        }

        // Hitung metrik ekuitas dan valuasi jika investor
        $totalShares     = $shareholder ? (int) $shareholder->total_shares : 0;
        $totalInvestment = $shareholder ? (float) $shareholder->total_investment : 0;

        $equitySummary = $this->shareholderService->getEquitySummary();
        $growthAnalytics = $this->analyticsService->getGrowth('ALL');

        $companyTotalShares = (int) ($equitySummary['total_shares'] ?? 0);
        $totalPercentage    = $companyTotalShares > 0 ? round(($totalShares / $companyTotalShares) * 100, 2) : 0;
        $companyValuation   = (float) ($growthAnalytics['estimated_valuation'] ?? 0);
        $myValuation        = ($totalPercentage / 100) * $companyValuation;

        return view('pages.profile.index', compact(
            'user',
            'shareholder',
            'totalShares',
            'totalInvestment',
            'totalPercentage',
            'myValuation',
            'companyValuation',
            'growthAnalytics'
        ));
    }

    /**
     * Update user profile information.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique'   => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        // Update juga record shareholder jika terhubung
        $shareholder = Shareholder::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if ($shareholder) {
            $shareholder->name  = $user->name;
            $shareholder->email = $user->email;
            if ($request->filled('phone')) {
                $shareholder->phone = $user->phone;
            }
            if ($request->filled('address')) {
                $shareholder->address = $request->address;
            }
            $shareholder->save();
        }

        return back()->with('success', 'Profil akun Anda berhasil diperbarui!');
    }
}
