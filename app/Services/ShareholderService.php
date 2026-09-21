<?php

namespace App\Services;

use App\Models\Shareholder;
use App\Models\ShareHolding;
use Illuminate\Support\Facades\DB;

class ShareholderService
{
    /**
     * Recalculate percentages for all active shareholdings in the system.
     */
    public function recalculatePercentages(): void
    {
        $totalCompanyShares = (int) ShareHolding::where('status', 'active')->sum('total_shares');
        if ($totalCompanyShares <= 0) {
            return;
        }

        $holdings = ShareHolding::where('status', 'active')->get();
        foreach ($holdings as $holding) {
            $percentage = round(($holding->total_shares / $totalCompanyShares) * 100, 2);
            $holding->percentage_share = $percentage;
            $holding->saveQuietly();
        }
    }

    /**
     * Get summary metrics of equity & shareholders.
     */
    public function getEquitySummary(): array
    {
        $totalShareholders = Shareholder::where('status', 'active')->count();
        $totalHoldings     = ShareHolding::where('status', 'active')->count();
        $totalShares       = (int) ShareHolding::where('status', 'active')->sum('total_shares');
        $totalInvestment   = (float) ShareHolding::where('status', 'active')->sum('total_investment');

        return [
            'total_shareholders' => $totalShareholders,
            'total_holdings'     => $totalHoldings,
            'total_shares'       => $totalShares,
            'total_investment'   => $totalInvestment,
            'nominal_par_base'   => 10000,
        ];
    }
}
