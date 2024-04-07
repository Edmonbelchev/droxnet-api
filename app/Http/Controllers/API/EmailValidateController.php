<?php
namespace App\Http\Controllers\API;

use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\EmailOtpVerification;

class EmailValidateController extends Controller
{
    /**
     * Validate the email address.
     */
    public function validateEmail(Request $request)
    {
        $user = auth()->user();

        $otp = (new Otp)->validate($user->email, $request->token);

        if($otp->status === true){
            $user->email_verified_at = now();
            $user->save();
        }

        return $otp;
    }

    /**
     * Generate a new token for the email address.
     */
    public function generateToken()
    {
        $user = auth()->user();

        $otp = (new Otp)->generate($user->email, 'numeric', 6, 15);

        if($otp->status === true){
            $user->notify(new EmailOtpVerification($otp->token));
        }

        return $otp;
    }
}
