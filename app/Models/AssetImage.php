<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssetImage extends Model
{
    use HasFactory;

    protected $table = 'asset_images';

    protected $fillable = [
        'asset_id',
        'image_path',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Relasi ke Asset pemilik gambar.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * URL publik file gambar.
     */
    public function getUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return asset('static/illustrations/undraw_empty.svg');
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Hapus file fisik dari storage ketika model dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function ($image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        });
    }
}
