<?php

namespace App\Exports;

use App\Models\Transfer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TransferExport implements FromView
{
    public function view(): View
    {
        return view('exports.transfer', [
            'transfers' => Transfer::with(['admin', 'investor.investor'])->get()
        ]);
    }
}
