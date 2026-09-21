<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceLogCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_client_code',
        'source_client_name',
        'event',
        'subject_type',
        'amount',
        'balance_type',
        'description',
        'log_created_at',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'log_created_at' => 'datetime',
        'raw_payload' => 'array',
    ];
}
