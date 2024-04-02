<?php

namespace App\Http\Controllers\API;

use App\Models\User;
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

        $profileImage = $user->profile_image; // Existing profile profile_image path

        // if ($request->hasFile('profile_image')) {
        //     // Get the uploaded file
        //     $profile_image = $request->file('profile_image');

        //     // Update the user's profile_image column with the URL
        //     $profileprofile_image = FileUploadHelper::imageUpload('profile_images/', $profile_image, [300, 300]);
        // }

        // Merge the updated profile_image URL with the rest of the validated data
        $validatedData = $request->validated();
        $validatedData['profile_image'] = $profileImage;

        // Update the user model with the merged data
        $user->update($validatedData);

        // Return the updated user profile
        return new ProfileResource($user);
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
