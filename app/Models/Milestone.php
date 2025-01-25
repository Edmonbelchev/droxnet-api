<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'proposal_id',
        'title',
        'description',
        'amount',
        'status',
        'due_date',
        'completed_at',
        'released_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_FUNDED = 'funded';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_RELEASED = 'released';
    public const STATUS_DISPUTED = 'disputed';

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }
}
