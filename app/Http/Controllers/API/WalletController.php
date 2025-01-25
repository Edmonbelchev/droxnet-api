<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Resources\WalletResource;
use App\Http\Resources\TransactionResource;
use App\Http\Controllers\Controller;

class WalletController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function show(): WalletResource
    {
        $user = auth()->user();
        $result = $this->paymentService->createOrGetWallet($user);

        return new WalletResource($result->load(['transactions', 'user']));
    }

    public function getTransactions(Request $request)
    {
        $user = auth()->user();
        $wallet = $this->paymentService->createOrGetWallet($user);
        $perPage = $request->get('per_page', 3);
        
        $result = $wallet->transactions()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return TransactionResource::collection($result);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $wallet = app(PaymentService::class)->createOrGetWallet(auth()->user());
            
            $transaction = app(PaymentService::class)->withdrawFromWallet(
                wallet: $wallet,
                amount: $request->amount
            );

            return response()->json([
                'message' => 'Withdrawal successful',
                'transaction' => $transaction,
                'new_balance' => $wallet->fresh()->balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Withdrawal failed: ' . $e->getMessage()
            ], 422);
        }
    }
}
