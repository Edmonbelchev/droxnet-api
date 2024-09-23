<?php

namespace App\Http\Controllers\API;

use App\Models\Job;
use App\Http\Controllers\Controller;
use App\Http\Resources\JobCollection;

class UserJobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke()
    {
        $user = auth()->user();

        $relations = [
            'user',
            'user.companyDetail'
        ];

        $perPage = request()->query('per_page', 15);

        $result = Job::orderByDesc('created_at');

        // If user is freelancer, get the jobs that has proposals from this user
        if ($user->role === 'freelancer') {
            $result->whereHas('proposals', function($query) use ($user) {
                $query->where('user_uuid', $user->uuid)->where('status', 'accepted');
            });
        } else {
            $relations[] = 'acceptedProposals.user';
            $relations[] = 'proposals';
            $relations[] = 'proposals.user';

            $result->where('user_uuid', $user->uuid);
        }

        if (request()->query('status')) {
            $result->where('status', request()->query('status'));
        }

        return JobCollection::make($result->with($relations)->paginate($perPage));
    }
}
