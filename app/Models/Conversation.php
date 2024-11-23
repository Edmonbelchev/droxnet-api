<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'freelancer_uuid',
        'employer_uuid',
    ];

    protected $with = ['freelancer', 'employer'];

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_uuid');
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_uuid');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function last_message()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
