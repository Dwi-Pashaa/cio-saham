<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'shareholder_id',
        'year',
        'initial_capital',
        'profit_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'year'            => 'integer',
        'initial_capital' => 'float',
        'profit_amount'   => 'float',
    ];

    /**
     * Relasi ke Pemegang Saham (Shareholder).
     */
    public function shareholder(): BelongsTo
    {
        return $this->belongsTo(Shareholder::class);
    }
}
