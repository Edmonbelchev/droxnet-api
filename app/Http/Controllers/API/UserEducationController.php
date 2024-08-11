<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserEducation;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Requests\UserEducationRequest;
use App\Http\Resources\UserEducationResource;
use App\Http\Resources\UserExperienceResource;

class UserEducationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Request user id and get user Educations
        if(!$request->user_uuid){
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $result = UserEducation::where('user_uuid', $request->user_uuid)->get();

        return UserEducationResource::collection($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserEducationRequest $request)
    {
        $user = auth()->user();

        $result = [];

        $educations = collect($request->validated()['educations']);

        // Create the educations
        $educations->each(function ($educationData) use ($user, &$result) {
            $result[] = $user->educations()->updateOrCreate(
                ['id' => $educationData['id']],
                $educationData
            );
        });

        // Extract the IDs of the educations that were created or updated
        $resultEducationIds = collect($result)->pluck('id')->all();

        // Retrieve the IDs of the user's current educations
        $currentEducationIds = $user->educations->pluck('id')->all();

        // Determine the IDs of educations to be deleted
        $educationsToDelete = array_diff($currentEducationIds, $resultEducationIds);

        // Delete the educations that are not in the result array
        UserEducation::destroy($educationsToDelete);

        return UserEducationResource::collection($result);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserEducationRequest $request, UserEducation $userEducation)
    {
        // Check if user education belongs to the authenticated user
        if ($userEducation->user_uuid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userEducation->update($request->validated());

        return UserEducationResource::make($userEducation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserEducation $userEducation)
    {
        // Check if user education belongs to the authenticated user
        if ($userEducation->user_uuid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userEducation->delete();

        return new StatusResource(true);
    }
}
