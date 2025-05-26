<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function __construct(
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Get the current balance of the platform's Stripe account
     */
    public function getBalance(): JsonResponse
    {
        try {
            $balance = \Stripe\Balance::retrieve();

            // Format the amounts
            $available = 0;
            $pending = 0;
            $currency = 'bgn'; // Default currency

            if (!empty($balance->available)) {
                foreach ($balance->available as $availableBalance) {
                    $available += $availableBalance->amount;
                    $currency = $availableBalance->currency;
                }
            }

            if (!empty($balance->pending)) {
                foreach ($balance->pending as $pendingBalance) {
                    $pending += $pendingBalance->amount;
                    $currency = $pendingBalance->currency;
                }
            }

            return response()->json([
                'data' => [
                    'available' => number_format($available / 100, 2, '.', ''),
                    'pending' => number_format($pending / 100, 2, '.', ''),
                    'currency' => strtoupper($currency)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve balance',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * List all balance transactions for the platform's Stripe account
     */
    public function getBalanceHistory(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $startingAfter = $request->get('starting_after');

            $params = [
                'limit' => $perPage,
                'expand' => ['data.source', 'data.source.customer']
            ];

            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }

            $balanceTransactions = \Stripe\BalanceTransaction::all($params);

            // Format the transactions
            $formattedTransactions = collect($balanceTransactions->data)->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => number_format($transaction->amount / 100, 2, '.', ''),
                    'currency' => strtoupper($transaction->currency),
                    'description' => $transaction->description,
                    'fee' => number_format($transaction->fee / 100, 2, '.', ''),
                    'fee_details' => $transaction->fee_details,
                    'net' => number_format($transaction->net / 100, 2, '.', ''),
                    'status' => $transaction->status,
                    'type' => $transaction->type,
                    'created' => date('Y-m-d H:i:s', $transaction->created),
                ];
            });

            $lastTransaction = end($balanceTransactions->data);

            return response()->json([
                'data' => $formattedTransactions,
                'meta' => [
                    'per_page' => $perPage,
                    'has_more' => $balanceTransactions->has_more,
                    'next_page_token' => $lastTransaction ? $lastTransaction->id : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve balance history',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get detailed information about a specific transaction
     */
    public function getTransactionDetails(Request $request): JsonResponse
    {
        try {
            $transactionId = $request->get('transaction_id');

            if (!$transactionId) {
                return response()->json([
                    'message' => 'Transaction ID is required'
                ], 400);
            }

            $transaction = \Stripe\BalanceTransaction::retrieve(
                $transactionId,
                ['expand' => ['source', 'source.customer', 'source.charge', 'source.payment_intent']]
            );

            $sourceData = null;
            if (is_object($transaction->source)) {
                $sourceData = [
                    'id' => $transaction->source->id ?? null,
                    'type' => $transaction->source->type ?? null,
                    'status' => $transaction->source->status ?? null,
                    'amount' => isset($transaction->source->amount) ? number_format($transaction->source->amount / 100, 2, '.', '') : null,
                    'currency' => isset($transaction->source->currency) ? strtoupper($transaction->source->currency) : null,
                    'description' => $transaction->source->description ?? null,
                ];

                if (isset($transaction->source->customer) && is_object($transaction->source->customer)) {
                    $sourceData['customer'] = [
                        'id' => $transaction->source->customer->id ?? null,
                        'email' => $transaction->source->customer->email ?? null,
                        'name' => $transaction->source->customer->name ?? null,
                    ];
                }

                if (isset($transaction->source->payment_method_details)) {
                    $sourceData['payment_method_details'] = $transaction->source->payment_method_details;
                }

                if (isset($transaction->source->payment_intent) && is_object($transaction->source->payment_intent)) {
                    $sourceData['payment_intent'] = [
                        'id' => $transaction->source->payment_intent->id ?? null,
                        'status' => $transaction->source->payment_intent->status ?? null,
                        'amount' => isset($transaction->source->payment_intent->amount) ? number_format($transaction->source->payment_intent->amount / 100, 2, '.', '') : null,
                        'currency' => isset($transaction->source->payment_intent->currency) ? strtoupper($transaction->source->payment_intent->currency) : null,
                    ];
                }
            }

            return response()->json([
                'data' => [
                    'id' => $transaction->id,
                    'amount' => number_format($transaction->amount / 100, 2, '.', ''),
                    'currency' => strtoupper($transaction->currency),
                    'description' => $transaction->description,
                    'fee' => number_format($transaction->fee / 100, 2, '.', ''),
                    'fee_details' => $transaction->fee_details,
                    'net' => number_format($transaction->net / 100, 2, '.', ''),
                    'status' => $transaction->status,
                    'type' => $transaction->type,
                    'created' => date('Y-m-d H:i:s', $transaction->created),
                    'source' => $sourceData
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Transaction error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve transaction details',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
