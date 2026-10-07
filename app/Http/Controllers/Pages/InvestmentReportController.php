<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\InvestmentReport;
use App\Models\Shareholder;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InvestmentReportController extends Controller
{
    /**
     * Tampilkan daftar laporan keuntungan saham tahunan.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->can('tambah laporan imbal hasil') || $user->can('ubah laporan imbal hasil'));

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

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->filter(function ($q) use ($request, $isAdmin) {
                    if ($isAdmin && $request->filled('shareholder_id')) {
                        $q->where('shareholder_id', $request->shareholder_id);
                    }

                    if ($request->filled('year')) {
                        $q->where('year', $request->year);
                    }

                    if ($request->filled('status')) {
                        $q->where('status', $request->status);
                    }

                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = trim($request->input('search.value'));
                        $q->where(function ($sub) use ($search) {
                            $sub->where('year', 'like', "%{$search}%")
                                ->orWhere('notes', 'like', "%{$search}%")
                                ->orWhereHas('shareholder', function ($shq) use ($search) {
                                    $shq->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->addColumn('year_badge', function ($rep) {
                    return '<span class="badge bg-blue-lt font-monospace fw-bold px-2 py-1">' . $rep->year . '</span>';
                })
                ->addColumn('shareholder_info', function ($rep) {
                    $name = $rep->shareholder ? e($rep->shareholder->name) : '-';
                    $initials = $rep->shareholder ? strtoupper(substr($rep->shareholder->name, 0, 2)) : 'PS';
                    $email = ($rep->shareholder && $rep->shareholder->email) ? '<span class="text-muted small d-block text-truncate" style="font-size: 0.75rem;">' . e($rep->shareholder->email) . '</span>' : '';

                    return '<div class="d-flex align-items-center gap-2">
                                <div class="shareholder-avatar-circle" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                    ' . $initials . '
                                </div>
                                <div class="overflow-hidden">
                                    <strong class="text-dark d-block text-truncate">' . $name . '</strong>
                                    ' . $email . '
                                </div>
                            </div>';
                })
                ->addColumn('initial_capital_formatted', function ($rep) {
                    return '<div class="text-end fw-bold font-monospace text-dark">Rp ' . number_format($rep->initial_capital, 0, ',', '.') . '</div>';
                })
                ->addColumn('profit_amount_formatted', function ($rep) {
                    return '<div class="text-end fw-bold font-monospace text-success">+ Rp ' . number_format($rep->profit_amount, 0, ',', '.') . '</div>';
                })
                ->addColumn('status_badge', function ($rep) {
                    if ($rep->status === 'distributed') {
                        return '<div class="text-center"><span class="badge bg-success-lt text-success fw-bold px-2 py-1">Dibagikan</span></div>';
                    } elseif ($rep->status === 'reinvested') {
                        return '<div class="text-center"><span class="badge bg-purple-lt text-purple fw-bold px-2 py-1">Direinvestasi</span></div>';
                    }
                    return '<div class="text-center"><span class="badge bg-warning-lt text-warning fw-bold px-2 py-1">Tertunda</span></div>';
                })
                ->addColumn('notes_formatted', function ($rep) {
                    return '<span class="text-muted small">' . e($rep->notes ?: '-') . '</span>';
                })
                ->addColumn('action', function ($rep) use ($user) {
                    $btnEdit = '';
                    if ($user && $user->can('ubah laporan imbal hasil')) {
                        $btnEdit = '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-report" data-id="' . $rep->id . '" title="Edit Laporan">
                                        Edit
                                    </button>';
                    }

                    $btnDelete = '';
                    if ($user && $user->can('hapus laporan imbal hasil')) {
                        $btnDelete = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-report" data-id="' . $rep->id . '" data-year="' . $rep->year . '" data-name="' . e($rep->shareholder->name ?? 'investor') . '" title="Hapus Laporan">
                                        Hapus
                                    </button>';
                    }

                    if (!$btnEdit && !$btnDelete) {
                        return '';
                    }

                    return '<div class="text-end"><div class="btn-group">' . $btnEdit . $btnDelete . '</div></div>';
                })
                ->rawColumns(['year_badge', 'shareholder_info', 'initial_capital_formatted', 'profit_amount_formatted', 'status_badge', 'notes_formatted', 'action'])
                ->make(true);
        }

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
     * Detail 1 laporan untuk modal AJAX.
     */
    public function show(Request $request, $id)
    {
        $report = InvestmentReport::with('shareholder')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'                 => $report->id,
                'shareholder_id'     => $report->shareholder_id,
                'year'               => $report->year,
                'initial_capital'    => number_format($report->initial_capital, 0, ',', '.'),
                'profit_amount'      => number_format($report->profit_amount, 0, ',', '.'),
                'status'             => $report->status,
                'notes'              => $report->notes,
                'update_url'         => route('investment-reports.update', $report->id),
            ]
        ]);
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
    public function destroy(Request $request, $id)
    {
        $report = InvestmentReport::findOrFail($id);
        $report->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Laporan keuntungan saham tahunan berhasil dihapus.'
            ]);
        }

        return redirect()->route('investment-reports.index')
            ->with('success', 'Laporan keuntungan saham tahunan berhasil dihapus.');
    }
}

