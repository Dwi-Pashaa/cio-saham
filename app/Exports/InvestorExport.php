<?php

namespace App\Exports;

use App\Models\Investor;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InvestorExport implements FromView
{
    public function view(): View
    {
        return view('exports.investor', [
            'investors' => Investor::whereHas('user.roles', function ($query) {
                                        $query->where('name', 'Investor');
                                    })
                                    ->with(['user', 'type', 'categorie'])
                                    ->get()
        ]);
    }
}
