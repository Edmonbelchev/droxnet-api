<?php

namespace App\Http\Controllers\API;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StripeAccountResource;

class StripeController extends Controller
{
    private $stripe;

    public function __construct(
        private readonly PaymentService $paymentService
    ) {
        $this->stripe = $paymentService->getStripeClient();
    }

    public function getAccountLink(): StripeAccountResource
    {
        $user = auth()->user();
        $wallet = $this->paymentService->createOrGetWallet($user);

        if (!$wallet->stripe_connect_id) {
            // Create the connect account if it doesn't exist
            $wallet->stripe_connect_id = $this->paymentService->createStripeConnectAccount($user);
            $wallet->save();
        }

        $result = $this->stripe->accountLinks->create([
            'account' => $wallet->stripe_connect_id,
            'refresh_url' => 'https://apt-sloth-factually.ngrok-free.app/stripe/refresh',
            'return_url' => 'http://localhost:3000/profile/payments',
            'type' => 'account_onboarding',
        ]);

        return new StripeAccountResource($result);
    }
}
