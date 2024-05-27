<?php

namespace App\Http\Controllers\API;

use App\Models\Skill;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SkillCollection;

class SkillController extends Controller
{
    public function index(SearchRequest $request)
    {
        $perPage = $request->query('per_page', 15);

        $result = Skill::query($perPage);

        if($request->query('excluded_skill'))
            $result->whereNotIn('id', $request->query('excluded_skill'));

        if (!$request->query('query'))
            return SkillCollection::make($result->paginate($perPage));

        return SkillCollection::make($result->where('name', 'like', "%{$request->query('query')}%")->paginate($perPage));
    }
}
