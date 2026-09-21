<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shareholder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'id_card_number',
        'address',
        'notes',
        'status',
    ];

    /**
     * Relasi ke User akun (jika terhubung).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi One-to-Many ke Kepemilikan Saham (ShareHoldings).
     * 1 Pemilik Saham bisa memiliki banyak saham.
     */
    public function holdings(): HasMany
    {
        return $this->hasMany(ShareHolding::class);
    }

    /**
     * Active holdings only.
     */
    public function activeHoldings(): HasMany
    {
        return $this->hasMany(ShareHolding::class)->where('status', 'active');
    }

    /**
     * Relasi ke Laporan Keuntungan Saham Tahunan (Investment Reports).
     */
    public function reports(): HasMany
    {
        return $this->hasMany(InvestmentReport::class)->orderBy('year', 'desc');
    }

    /**
     * Accessor: Total lembar saham milik pemegang saham ini (akumulasi semua saham).
     */
    public function getTotalSharesAttribute(): int
    {
        return (int) $this->holdings()->where('status', 'active')->sum('total_shares');
    }

    /**
     * Accessor: Total nilai nominal investasi milik pemegang saham ini.
     */
    public function getTotalInvestmentAttribute(): float
    {
        return (float) $this->holdings()->where('status', 'active')->sum('total_investment');
    }

    /**
     * Accessor: Total persentase kepemilikan saham kumulatif.
     */
    public function getTotalPercentageAttribute(): float
    {
        return (float) $this->holdings()->where('status', 'active')->sum('percentage_share');
    }

    /**
     * Accessor: Jumlah portofolio/saham yang dimiliki.
     */
    public function getPortfoliosCountAttribute(): int
    {
        return $this->holdings()->where('status', 'active')->count();
    }
}
