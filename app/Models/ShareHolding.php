<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShareHolding extends Model
{
    use HasFactory;

    protected $fillable = [
        'shareholder_id',
        'share_code',
        'entity_name',
        'total_shares',
        'nominal_value_per_share',
        'total_investment',
        'percentage_share',
        'certificate_number',
        'acquisition_date',
        'status',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'total_shares' => 'integer',
        'nominal_value_per_share' => 'decimal:2',
        'total_investment' => 'decimal:2',
        'percentage_share' => 'decimal:2',
    ];

    /**
     * Relasi ke Pemilik Saham (Parent).
     */
    public function shareholder(): BelongsTo
    {
        return $this->belongsTo(Shareholder::class);
    }

    /**
     * Relasi ke Riwayat Transaksi Saham.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(ShareTransaction::class);
    }
}
