<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'job_id',
        'type',
        'amount',
        'currency',
        'status',
        'stripe_payment_id',
        'stripe_transfer_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'json',
    ];

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_ESCROW_HOLD = 'escrow_hold';
    public const TYPE_ESCROW_RELEASE = 'escrow_release';
    public const TYPE_REFUND = 'refund';
    public const TYPE_PAYOUT = 'payout';
    
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DISPUTED = 'disputed';
    public const STATUS_CANCELED = 'canceled';

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
