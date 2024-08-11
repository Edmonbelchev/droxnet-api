<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserExperience;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Requests\UserExperienceRequest;
use App\Http\Resources\UserExperienceResource;

class UserExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Request user id and get user experiences
        if (!$request->user_uuid) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $result = UserExperience::where('user_uuid', $request->user_uuid)->get();

        return UserExperienceResource::collection($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserExperienceRequest $request)
    {
        $user = auth()->user();

        $result = [];

        $experiences = collect($request->validated()['experiences']);

        // Create the experiences
        $experiences->each(function ($experienceData) use ($user, &$result) {
            $result[] = $user->experiences()->updateOrCreate(
                ['id' => $experienceData['id']],
                $experienceData
            );
        });

        // Extract the IDs of the experiences that were created or updated
        $resultExperienceIds = collect($result)->pluck('id')->all();

        // Retrieve the IDs of the user's current experiences
        $currentExperienceIds = $user->experiences->pluck('id')->all();

        // Determine the IDs of experiences to be deleted
        $experiencesToDelete = array_diff($currentExperienceIds, $resultExperienceIds);

        // Delete the experiences that are not in the result array
        UserExperience::destroy($experiencesToDelete);

        return UserExperienceResource::collection($result);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserExperienceRequest $request, UserExperience $userExperience)
    {
        // Check if user experience belongs to the authenticated user
        if ($userExperience->user_uuid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userExperience->update($request->validated());

        return UserExperienceResource::make($userExperience);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserExperience $userExperience)
    {
        // Check if user experience belongs to the authenticated user
        if ($userExperience->user_uuid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userExperience->delete();

        return new StatusResource(true);
    }
}
