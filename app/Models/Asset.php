<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'name',
        'type',
        'price',
        'serial_number',
        'mac_address',
        'owner_type',
        'shareholder_id',
        'owner_name',
        'purchase_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'purchase_date' => 'date',
    ];

    /**
     * Relasi ke Koleksi Foto / Gambar Aset (Multiple Images).
     */
    public function images(): HasMany
    {
        return $this->hasMany(AssetImage::class, 'asset_id');
    }

    /**
     * Foto Utama Aset (Primary Image).
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(AssetImage::class, 'asset_id')->where('is_primary', true)->latestOfMany();
    }

    /**
     * Ambil thumbnail URL foto pertama atau default placeholder.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $firstImage = $this->images->first();
        return $firstImage ? $firstImage->url : null;
    }

    /**
     * Relasi ke Pemegang Saham / Investor (opsional).
     */
    public function shareholder(): BelongsTo
    {
        return $this->belongsTo(Shareholder::class, 'shareholder_id');
    }

    /**
     * Relasi ke User pembuat/pencatat aset.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Format harga dalam Rupiah.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->price, 0, ',', '.');
    }

    /**
     * Label pemilik aset.
     */
    public function getOwnerLabelAttribute(): string
    {
        if ($this->owner_type === 'shareholder') {
            return $this->shareholder?->name ?? $this->owner_name ?? 'Pemegang Saham';
        }

        return $this->owner_name ?: 'PT CIO NETWORK SOLUTION';
    }

    /**
     * Format tanggal pembelian.
     */
    public function getFormattedPurchaseDateAttribute(): string
    {
        return $this->purchase_date ? $this->purchase_date->format('d M Y') : '-';
    }

    /**
     * Cascade delete images saat aset dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function ($asset) {
            foreach ($asset->images as $image) {
                $image->delete();
            }
        });
    }
}
