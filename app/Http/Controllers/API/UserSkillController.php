<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserSkillsCollection;

class UserSkillController extends Controller
{

    public function __invoke()
    {
        return UserSkillsCollection::make(auth()->user()->skills);
    }
}
