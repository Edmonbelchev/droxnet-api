<?php

namespace App\Http\Controllers\Api;

use App\Models\File;

use App\Models\UserAward;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Requests\UserAwardRequest;
use App\Http\Resources\UserAwardResource;

class UserAwardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Request user id and get user experiences
        if (!$request->user_id) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $user = auth()->user();

        return UserAwardResource::collection($user->awards);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserAwardRequest $request)
    {
        $user = auth()->user();

        $result = [];

        $awards = collect($request->validated()['awards']);

        // Create the awards
        $awards->each(function ($awardData) use ($user, &$result) {
            // Create or update the files
            $award = $user->awards()->updateOrCreate([
                'id' => $awardData['id']
            ], $awardData);

            if (isset($awardData['files']) && is_array($awardData['files'])) {
                // Create the files
                $files = collect($awardData['files'])->map(function ($filePath) use ($award) {
                    return new File(
                        [
                            'filebale_id' => $award->id,
                            'fileble_type' => UserAward::class,
                            'url' => $filePath
                        ]
                    );
                });
                $award->files()->delete();
                $award->files()->saveMany($files);
            }

            $result[] = $award;
        });

        // Extract the IDs of the awards that were created or updated
        $resultAwardIds = collect($result)->pluck('id')->all();

        // Retrieve the IDs of the user's current awards
        $currentAwardIds = $user->awards->pluck('id')->all();

        // Determine the IDs of awards to be deleted
        $awardsToDelete = array_diff($currentAwardIds, $resultAwardIds);

        // Delete the awards that are not in the result array
        UserAward::destroy($awardsToDelete);

        return UserAwardResource::collection($result);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserAward $userAward)
    {
        // Check if the award belongs to the user
        if ($userAward->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userAward->delete();

        return new StatusResource(true);
    }
}
