<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Notifications\PasswordResetNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasUuids;

    protected $primaryKey = 'uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'tagline',
        'email',
        'password',
        'role',
        'gender',
        'phone',
        'country',
        'city',
        'about',
        'date_of_birth',
        'profile_image',
        'profile_banner',
        'hourly_rate',
        'email_verified_at',
    ];

    protected $with = ['skills'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'uuid' => 'string'
        ];
    }

    // Scope for freelancer
    public function scopeFreelancer($query)
    {
        return $query->where('role', 'freelancer');
    }

    // Scope for employer
    public function scopeEmployer($query)
    {
        return $query->where('role', 'employer');
    }

    // Relation for user's company detail
    public function companyDetail()
    {
        return $this->hasOne(CompanyDetail::class);
    }

    // Relation for user's skills
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skills')->using(UserSkill::class)->withPivot('rate');
    }

    // Relation for user's experience
    public function experiences()
    {
        return $this->hasMany(UserExperience::class);
    }

    // Relation for user's education
    public function educations()
    {
        return $this->hasMany(UserEducation::class);
    }

    // Relation for user's projects
    public function projects()
    {
        return $this->hasMany(UserProject::class);
    }

    // Relation for user's awards
    public function awards()
    {
        return $this->hasMany(UserAward::class);
    }

    // Relation for user's deleted profile
    public function deletedProfile()
    {
        return $this->hasOne(DeletedUser::class);
    }

    // Relation for user's jobs
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    // Relation for user's reports
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // Relation for user's job proposals
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    // Relation for user of type employer's job proposals
    public function employerProposals()
    {
        return $this->hasManyThrough(Proposal::class, Job::class);
    }

    // Relation for user's saved items
    public function savedItems()
    {
        return $this->hasMany(SavedItem::class, 'user_uuid', 'uuid');
    }

    // Relation for user's conversations
    public function conversations()
    {
        if ($this->role === 'freelancer') {
            return $this->hasMany(Conversation::class, 'freelancer_uuid', 'uuid');
        }

        return $this->hasMany(Conversation::class, 'employer_uuid', 'uuid');
    }

    // Relation for user's messages
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_uuid', 'uuid');
    }

    // Relation for user's wallet
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Check if the user is saved by the authenticated user.
     */
    public function savedItem()
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        // Check if the user record is saved by the authenticated user
        $savedItem = $user->savedItems()->where('saveable_id', $this->id)->where('saveable_type', 'user')->first();
        
        return $savedItem;
    }

    /**
     * Check if the user is reported by the authenticated user.
     */
    public function isReported(): ?bool
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        // Check if the user record is reported by the authenticated user
        return $user->reports()->where('reportable_id', $this->id)->where('reportable_type', 'user')->exists();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }
}
