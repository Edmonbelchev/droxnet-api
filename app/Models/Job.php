<?php

namespace App\Models;

use App\Models\JobSkill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'budget',
        'duration',
        'status',
        'user_uuid',
        'category_id',
        'location',
        'type',
        'level',
        'languages',
        'show_attachments',
    ];

    protected $casts = [
        'languages' => 'array',
    ];

    /**
     * Get the user that owns the job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the skills for the job.
     */
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_skills')->using(JobSkill::class);
    }

    /**
     * Get the files for the job.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
