<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'gender',
        'phone',
        'country',
        'city',
        'about',
        'date_of_birth',
        'profile_image',
        'profile_banner',
        'hourly_rate',
        'company_name',
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
        ];
    }

    // Scope for user's role
    public function role()
    {
        return $this->hasOne(UserRole::class);
    }

    // Scope for user's role
    public function isFreelancer(): bool
    {
        return $this->role->role->name === 'freelancer';
    }

    // Scope for user's role
    public function isEmployer(): bool
    {
        return $this->role->role->name === 'employer';
    }

    // Scope for user's skills
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skills')->using(UserSkill::class)->withPivot('rate');
    }
}
