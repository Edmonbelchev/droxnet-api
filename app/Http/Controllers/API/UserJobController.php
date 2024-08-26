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

        $perPage = request()->query('per_page', 15);

        $result = Job::where('user_uuid', $user->uuid)
            ->with(['proposals', 'proposals.user', 'user', 'user.companyDetail']);

        return JobCollection::make($result->paginate($perPage));
    }
}
