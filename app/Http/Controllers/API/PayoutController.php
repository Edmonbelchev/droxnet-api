<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class PayoutController extends Controller
{
    public function __construct(
        private readonly PayoutService $payoutService
    ) {}

    public function configureFreelancerPayouts(Request $request): JsonResponse
    {
        $request->validate([
            'interval' => 'required|in:manual,daily,weekly,monthly',
            'weekly_anchor' => 'required_if:interval,weekly|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'monthly_anchor' => 'required_if:interval,monthly|integer|between:1,31'
        ]);

        try {
            $options = [];
            if ($request->input('interval') === 'weekly') {
                $options['weekly_anchor'] = $request->input('weekly_anchor');
            }
            if ($request->input('interval') === 'monthly') {
                $options['monthly_anchor'] = $request->input('monthly_anchor');
            }

            $this->payoutService->configureFreelancerPayouts(
                $request->user(),
                $request->input('interval'),
                $options
            );

            return response()->json([
                'message' => 'Payout schedule configured successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to configure payout schedule',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getEarningsReport(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        $report = $this->payoutService->getFreelancerEarningsReport(
            $request->user(),
            $startDate,
            $endDate
        );

        return response()->json($report);
    }

    public function getPayoutReport(Request $request): JsonResponse
    {
        $this->authorize('viewPayoutReport', User::class);

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        $report = $this->payoutService->getPayoutReport($startDate, $endDate);

        return response()->json($report);
    }
}
