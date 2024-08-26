<?php

namespace App\Models;

use App\JobTypeEnum;
use App\JobLevelEnum;
use App\JobDurationEnum;
use App\Models\JobSkill;
use App\JobBudgetTypeEnum;
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
        'budget_type',
        'duration',
        'status',
        'user_uuid',
        'category_id',
        'location',
        'country',
        'type',
        'level',
        'languages',
        'show_attachments',
        'status'
    ];

    protected $casts = [
        'languages'        => 'array',
        'show_attachments' => 'boolean',
        'duration'         => JobDurationEnum::class,
        'type'             => JobTypeEnum::class,
        'budget_type'      => JobBudgetTypeEnum::class,
        'level'            => JobLevelEnum::class,
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

    /**
     * Get the proposals for the job.
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }
}
