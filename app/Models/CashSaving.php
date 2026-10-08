<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashSaving extends Model
{
    use HasFactory;

    protected $table = 'cash_savings';

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'amount',
        'recipient_name',
        'bank_name',
        'account_number',
        'notes',
        'proof_file',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
    ];

    /**
     * User yang mencatat transaksi alokasi tabungan ini.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * URL untuk file bukti transfer/setoran.
     */
    public function getProofUrlAttribute(): ?string
    {
        if (!$this->proof_file) {
            return null;
        }

        if (str_starts_with($this->proof_file, 'http')) {
            return $this->proof_file;
        }

        return asset('storage/' . $this->proof_file);
    }

    /**
     * Generator otomatis nomor transaksi (SAV-YYYYMM-XXXX).
     */
    public static function generateTransactionNumber(): string
    {
        $prefix = 'SAV-' . date('Ym') . '-';
        $lastRecord = self::where('transaction_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord && preg_match('/-(\d+)$/', $lastRecord->transaction_number, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
