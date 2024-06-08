<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProject extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'url'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($userProject) {
            // Delete the associated files
            $userProject->files()->delete();
        });
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
