<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Resources\ProfileResource;
use App\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new ProfileResource(auth()->user());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        // Update the user model with the merged data
        $user->update($request->validated());

        // Sync user skills with correct array format
        $skillsData = collect($request->skills)->mapWithKeys(function ($skill) {
            return [
                $skill['id'] => [
                    'rate' => $skill['rate']
                ]
            ];
        })->toArray();

        $user->skills()->sync($skillsData);

        // Return the updated user profile
        return new ProfileResource($user->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        $user = auth()->user();

        User::where('id', $user->id)->delete();

        return new StatusResource(true);
    }
}
