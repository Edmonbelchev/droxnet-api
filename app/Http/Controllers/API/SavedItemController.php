<?php

namespace App\Http\Controllers\API;

use App\Models\Job;
use App\Models\User;
use App\Models\SavedItem;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\JobCollection;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserCollection;
use App\Http\Requests\SavedItemRequest;
use App\Http\Resources\SavedItemResource;
use App\Http\Resources\SavedItemCollection;
use App\Http\Requests\SearchSavedItemRequest;

class SavedItemController extends Controller
{
    public function index(SearchSavedItemRequest $request)
    {
        $user = auth()->user();

        $perPage = $request->per_page ?? 10;

        $savedItems = SavedItem::where('user_uuid', $user->uuid)->pluck('saveable_id');

        $savedItems->where('saveable_type', $request->type);

        if ($request->type === 'user') {
            $users = User::whereIn('id', $savedItems);

            if ($request->is_company) {
                $users->where('role', 'employer');
                $users->with('companyDetail');
            } else {
                $users->where('role', 'freelancer');
            }

            return new UserCollection($users->paginate($perPage));
        } else if ($request->type === 'job') {
            $jobs = Job::whereIn('id', $savedItems)->paginate($perPage);

            return new JobCollection($jobs);
        }

        return new SavedItemCollection($savedItems->paginate($perPage));
    }

    public function store(SavedItemRequest $request)
    {
        $user = auth()->user();

        $savedItem = SavedItem::create([
            'user_uuid'     => $user->uuid,
            'saveable_id'   => $request->saveable_id,
            'saveable_type' => $request->saveable_type,
        ]);

        return new SavedItemResource($savedItem);
    }

    public function destroy(SavedItem $savedItem)
    {
        Gate::authorize('delete', $savedItem);

        $savedItem->delete();

        return new StatusResource(true);
    }
}
