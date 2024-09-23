<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\SimpleUserCollection;
use App\Http\Requests\CompanyFollowersRequest;

class CompanyController extends Controller
{
    function followers(CompanyFollowersRequest $request)
    {
        $perPage = request()->query('per_page', 15);

        // Retrueve all users that follow the company(employer) from saved items table
        $users = User::whereHas('savedItems', function ($query) use ($request) {
            $query->where('saveable_id', $request->company_id)
                ->where('saveable_type', 'user');
        })->where('role', 'freelancer');

        return SimpleUserCollection::make($users->paginate($perPage));
    }
}
