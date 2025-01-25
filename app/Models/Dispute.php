<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    protected $fillable = [
        'job_id',
        'milestone_id',
        'raised_by',
        'raised_against',
        'type',
        'status',
        'description',
        'resolution',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public const TYPE_PAYMENT = 'payment';
    public const TYPE_QUALITY = 'quality';
    public const TYPE_COMMUNICATION = 'communication';
    public const TYPE_OTHER = 'other';

    public const STATUS_OPEN = 'open';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function raisedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function raisedAgainstUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_against');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
