<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\StatusResource;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthTokenResource;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    function register(RegisterRequest $request): AuthTokenResource
    {
        $token = null;

        $user  = new User($request->validated());

        $user->forceFill([
            'password' => Hash::make($request->get('password'))
        ]);

        DB::transaction(function () use (&$user, $request, &$token) {
            $user->save();
            $token  = $user->createToken("API TOKEN");

            event(new Registered($user));
        });

        return AuthTokenResource::make($token);
    }

    /**
     * @throws ValidationException
     */
    function login(LoginRequest $request): AuthTokenResource
    {
        /** @var User $user */
        $user = User::query()->where('email', $request->get('email'))->first();

        $password = $request->get('password');
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token  = $user->createToken("API TOKEN");

        return AuthTokenResource::make($token);
    }

    public function logout(Request $request): StatusResource
    {
        /** @var User $user */
        $user = $request->user();

        /** @var PersonalAccessToken $accessToken */
        $accessToken = $user->currentAccessToken();
        $accessToken->delete();

        return StatusResource::make(true);
    }
}
