<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Requests\UserSearchRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserSearchRequest $request)
    {
        $perPage = $request->query('per_page', 15);

        $result = User::freelancer();

        if ($request->has('countries')) {
            $result->whereIn('country', $request->countries);
        }

        if ($request->has('skills')) {
            $result->whereHas('skills', function ($query) use ($request) {
                $query->whereIn('skills.id', $request->skills);
            });
        }

        if ($request->has('hourly_rate')) {
            $result->whereBetween('hourly_rate', [
                $request->input('hourly_rate.start'),
                $request->input('hourly_rate.end'),
            ]);
        }

        return UserCollection::make($result->paginate($perPage));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource
    {
        // Load all relations
        $user->load('skills', 'educations', 'experiences', 'projects', 'awards');

        return UserResource::make($user);
    }
}
