<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSkill extends Pivot
{
    use HasFactory;

    protected $table = 'user_skills';

    protected $casts = [
        'rate' => 'int',
    ];
}
