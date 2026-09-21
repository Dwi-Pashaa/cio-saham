<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\InvestmentReport;
use App\Models\Shareholder;
use Illuminate\Http\Request;

class InvestmentReportController extends Controller
{
    /**
     * Tampilkan daftar laporan keuntungan saham tahunan.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->can('create-investment-report');

        $query = InvestmentReport::with('shareholder')
            ->orderBy('year', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        // Jika user adalah Pemegang Saham biasa (bukan admin) -> Hanya tampilkan laporan miliknya
        $currentShareholder = null;
        if (!$isAdmin) {
            $currentShareholder = Shareholder::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();

            if (!$currentShareholder) {
                $currentShareholder = Shareholder::where('name', 'like', '%' . $user->name . '%')->first();
            }

            if ($currentShareholder) {
                $query->where('shareholder_id', $currentShareholder->id);
            } else {
                $query->whereRaw('1 = 0'); // Belum ada profil shareholder terhubung
            }
        }

        // Filter untuk Admin
        if ($isAdmin) {
            if ($request->filled('shareholder_id')) {
                $query->where('shareholder_id', $request->shareholder_id);
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->whereHas('shareholder', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(15)->withQueryString();

        // Metrik Ringkasan
        if ($isAdmin) {
            $totalCapital = InvestmentReport::sum('initial_capital');
            $totalProfit  = InvestmentReport::sum('profit_amount');
            $totalReports = InvestmentReport::count();
        } else {
            $totalCapital = $currentShareholder ? InvestmentReport::where('shareholder_id', $currentShareholder->id)->sum('initial_capital') : 0;
            $totalProfit  = $currentShareholder ? InvestmentReport::where('shareholder_id', $currentShareholder->id)->sum('profit_amount') : 0;
            $totalReports = $currentShareholder ? InvestmentReport::where('shareholder_id', $currentShareholder->id)->count() : 0;
        }

        // Data Pemegang Saham untuk dropdown Admin
        $allShareholders = $isAdmin ? Shareholder::orderBy('name', 'asc')->get() : collect();

        // Daftar tahun yang tersedia untuk filter
        $availableYears = InvestmentReport::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        return view('pages.investment-reports.index', compact(
            'reports',
            'isAdmin',
            'currentShareholder',
            'totalCapital',
            'totalProfit',
            'totalReports',
            'allShareholders',
            'availableYears'
        ));
    }

    /**
     * Simpan laporan keuntungan saham baru (Admin Only).
     */
    public function store(Request $request)
    {
        // Sanitasi nilai Rupiah (bersihkan titik pemisah ribuan)
        if ($request->has('initial_capital')) {
            $cleanedCapital = preg_replace('/[^0-9.-]/', '', str_replace('.', '', (string) $request->initial_capital));
            $request->merge(['initial_capital' => $cleanedCapital]);
        }
        if ($request->has('profit_amount')) {
            $cleanedProfit = preg_replace('/[^0-9.-]/', '', str_replace('.', '', (string) $request->profit_amount));
            $request->merge(['profit_amount' => $cleanedProfit]);
        }

        $request->validate([
            'shareholder_id'  => 'required|exists:shareholders,id',
            'year'            => 'required|integer|min:2000|max:2099',
            'initial_capital' => 'required|numeric|min:0',
            'profit_amount'   => 'required|numeric',
            'status'          => 'nullable|string|in:distributed,pending,reinvested',
            'notes'           => 'nullable|string|max:1000',
        ], [
            'shareholder_id.required'  => 'Pilih pemegang saham terlebih dahulu.',
            'shareholder_id.exists'    => 'Pemegang saham yang dipilih tidak valid.',
            'year.required'            => 'Tahun periode investasi wajib diisi.',
            'initial_capital.required' => 'Nominal saham / modal awal wajib diisi.',
            'profit_amount.required'   => 'Nilai keuntungan setelah 1 tahun wajib diisi.',
        ]);

        InvestmentReport::create([
            'shareholder_id'  => $request->shareholder_id,
            'year'            => (int) $request->year,
            'initial_capital' => (float) $request->initial_capital,
            'profit_amount'   => (float) $request->profit_amount,
            'status'          => $request->status ?? 'distributed',
            'notes'           => $request->notes,
        ]);

        return redirect()->route('investment-reports.index')
            ->with('success', 'Laporan keuntungan saham tahunan berhasil ditambahkan.');
    }

    /**
     * Perbarui laporan keuntungan saham (Admin Only).
     */
    public function update(Request $request, $id)
    {
        $report = InvestmentReport::findOrFail($id);

        // Sanitasi nilai Rupiah (bersihkan titik pemisah ribuan)
        if ($request->has('initial_capital')) {
            $cleanedCapital = preg_replace('/[^0-9.-]/', '', str_replace('.', '', (string) $request->initial_capital));
            $request->merge(['initial_capital' => $cleanedCapital]);
        }
        if ($request->has('profit_amount')) {
            $cleanedProfit = preg_replace('/[^0-9.-]/', '', str_replace('.', '', (string) $request->profit_amount));
            $request->merge(['profit_amount' => $cleanedProfit]);
        }

        $request->validate([
            'shareholder_id'  => 'required|exists:shareholders,id',
            'year'            => 'required|integer|min:2000|max:2099',
            'initial_capital' => 'required|numeric|min:0',
            'profit_amount'   => 'required|numeric',
            'status'          => 'nullable|string|in:distributed,pending,reinvested',
            'notes'           => 'nullable|string|max:1000',
        ], [
            'shareholder_id.required'  => 'Pilih pemegang saham terlebih dahulu.',
            'year.required'            => 'Tahun periode investasi wajib diisi.',
            'initial_capital.required' => 'Nominal saham / modal awal wajib diisi.',
            'profit_amount.required'   => 'Nilai keuntungan setelah 1 tahun wajib diisi.',
        ]);

        $report->update([
            'shareholder_id'  => $request->shareholder_id,
            'year'            => (int) $request->year,
            'initial_capital' => (float) $request->initial_capital,
            'profit_amount'   => (float) $request->profit_amount,
            'status'          => $request->status ?? 'distributed',
            'notes'           => $request->notes,
        ]);

        return redirect()->route('investment-reports.index')
            ->with('success', 'Laporan keuntungan saham tahunan berhasil diperbarui.');
    }

    /**
     * Hapus laporan keuntungan saham (Admin Only).
     */
    public function destroy($id)
    {
        $report = InvestmentReport::findOrFail($id);
        $report->delete();

        return redirect()->route('investment-reports.index')
            ->with('success', 'Laporan keuntungan saham tahunan berhasil dihapus.');
    }
}
