<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_uuid',
        'balance',
        'escrow_balance',
        'currency',
        'stripe_customer_id',
        'stripe_connect_id',
        'pending_balance',
        'last_sync',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'escrow_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'last_sync' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
