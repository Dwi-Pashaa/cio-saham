<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'share_holding_id',
        'transaction_type',
        'shares_amount',
        'price_per_share',
        'total_amount',
        'transaction_date',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'shares_amount' => 'integer',
        'price_per_share' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Relasi ke Kepemilikan Saham.
     */
    public function shareHolding(): BelongsTo
    {
        return $this->belongsTo(ShareHolding::class);
    }
}
