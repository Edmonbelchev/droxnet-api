<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\StatusResource;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Requests\DeleteProfileRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->employer()) {
            $user->load('companyDetail');
        }

        return new ProfileResource($user);
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

        // Check if user is employer and update company details
        if ($user->role === 'employer') {
            $user->companyDetail()->updateOrCreate(
                ['user_uuid' => $user->uuid],
                $request->company_details
            );
        }

        // Return the updated user profile
        return new ProfileResource($user->fresh('companyDetail'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteProfileRequest $request)
    {
        $user = auth()->user();

        User::where('id', $user->id)->delete();

        // Create a new deleted user record
        $user->deletedProfile()->create([
            'reason'      => $request->reason,
            'description' => $request->description
        ]);

        return new StatusResource(true);
    }

    /**
     * Set new password for the user.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $auth = auth()->user();

        $user           =  User::find($auth->id);
        $user->password =  Hash::make($request->new_password);
        $user->save();

        return new StatusResource(true);
    }

    /**
     * Forgot password
     * Send email to user with reset password link
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? new StatusResource(true)
            : new StatusResource(false);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);

                $user->save();
            }
        );


        return $status === Password::PASSWORD_RESET
            ? new StatusResource(true, __($status))
            : new StatusResource(false, __($status));
    }
}
