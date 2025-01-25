<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Job;
use App\Models\Milestone;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Models\UserPaymentMethod;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\MilestoneRequest;
use Stripe\Exception\ApiErrorException;
use App\Http\Requests\AddPaymentMethodRequest;
use App\Http\Resources\UserPaymentMethodResource;
use App\Http\Resources\UserPaymentMethodCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PaymentService $paymentService
    ) {
        // Individual authorization will be handled in each method
    }

    public function deposit(DepositRequest $request): JsonResponse
    {
        try {
            $wallet = $this->paymentService->createOrGetWallet($request->user());
            $transaction = $this->paymentService->depositToWallet(
                $wallet,
                $request->validated('amount'),
                $request->validated('payment_method_id')
            );

            return response()->json([
                'message' => 'Deposit successful',
                'transaction' => $transaction
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'message' => 'Payment failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function createMilestone(MilestoneRequest $request, Job $job): JsonResponse
    {
        try {
            // $this->authorize('createMilestone', [Milestone::class, $job]);
            
            $milestone = $this->paymentService->createMilestone($job, $request->validated());

            return response()->json([
                'message' => 'Milestone created successfully',
                'milestone' => $milestone
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to create milestone',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function fundMilestone(Milestone $milestone): JsonResponse
    {
        try {
            // $this->authorize('fundMilestone', $milestone);
            
            $transaction = $this->paymentService->fundMilestone($milestone);

            return response()->json([
                'message' => 'Milestone funded successfully',
                'transaction' => $transaction
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fund milestone',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function releaseMilestonePayment(Milestone $milestone): JsonResponse
    {
        try {
            // $this->authorize('release', $milestone);

            // Check milestone status and funding
            if ($milestone->status !== Milestone::STATUS_FUNDED) {
                return response()->json([
                    'message' => 'Milestone must be funded before release',
                    'status' => $milestone->status,
                    'amount' => $milestone->amount
                ], 400);
            }

            $transaction = $this->paymentService->releaseMilestonePayment($milestone);

            return response()->json([
                'message' => 'Payment released successfully',
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to release payment',
                'error' => $e->getMessage(),
                'milestone_status' => $milestone->status,
                'milestone_amount' => $milestone->amount
            ], 400);
        }
    }

    public function addPaymentMethod(AddPaymentMethodRequest $request): UserPaymentMethodResource
    {
        try {
            $paymentMethod = $this->paymentService->addPaymentMethod(
                $request->user(),
                $request->validated('payment_method_id')
            );


            return $paymentMethod;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add payment method',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getPaymentMethods(Request $request): UserPaymentMethodCollection
    {
        $paymentMethods = $this->paymentService->getPaymentMethods($request->user());

        return $paymentMethods;
    }
}
