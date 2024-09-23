<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SavedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_uuid',
        'saveable_id',
        'saveable_type',
    ];

    public function saveable()
    {
        return $this->morphTo();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'saveable');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'saveable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
