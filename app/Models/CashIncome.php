<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CashIncome extends Model
{
    use HasFactory;

    protected $table = 'cash_incomes';

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'sender_name',
        'bank_name',
        'account_number',
        'amount',
        'has_admin_fee',
        'admin_fee',
        'net_amount',
        'proof_file',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
        'has_admin_fee'    => 'boolean',
        'admin_fee'        => 'decimal:2',
        'net_amount'       => 'decimal:2',
    ];

    /**
     * User who recorded this income transaction.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get accessible URL for uploaded proof file.
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
     * Generate automatic transaction number (INC-YYYYMM-XXXX).
     */
    public static function generateTransactionNumber(): string
    {
        $prefix = 'INC-' . date('Ym') . '-';
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
