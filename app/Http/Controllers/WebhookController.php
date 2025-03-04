<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Exception;
use Stripe\Event;
use Stripe\Webhook;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\PaymentService;
use App\Notifications\PayoutFailed;
use Illuminate\Support\Facades\Log;
use App\Notifications\PaymentFailed;
use App\Notifications\PayoutCanceled;
use App\Notifications\PaymentReceived;
use App\Notifications\PaymentSuccessful;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function handleWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (empty($sigHeader)) {
            Log::error('Webhook signature header missing');
            return response('Webhook signature header missing', 400);
        }

        if (empty($payload)) {
            Log::error('Webhook payload empty');
            return response('Webhook payload empty', 400);
        }

        $webhookSecret = config('services.stripe.webhook_secret');
        if (empty($webhookSecret)) {
            Log::error('Stripe webhook secret not configured');
            return response('Webhook secret not configured', 500);
        }

        try {
            Log::info('Attempting to verify webhook', [
                'signature' => $sigHeader,
                'payload_length' => strlen($payload)
            ]);

            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed.', [
                'error' => $e->getMessage(),
                'signature' => $sigHeader
            ]);
            return response('Signature verification failed', 400);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response('Webhook processing failed', 500);
        }

        $this->handleStripeEvent($event);

        return response('', 200);
    }

    protected function handleStripeEvent(Event $event): void
    {
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            case 'transfer.succeeded':
                $this->handleTransferSucceeded($event->data->object);
                break;

            case 'transfer.failed':
                $this->handleTransferFailed($event->data->object);
                break;

            case 'payout.created':
            case 'payout.updated':
            case 'payout.paid':
            case 'payout.failed':
            case 'payout.canceled':
                $this->handlePayoutEvent($event);
                break;

            default:
                Log::info('Unhandled Stripe event.', ['type' => $event->type]);
        }
    }

    protected function handlePayoutEvent(Event $event): void
    {
        $payout = $event->data->object;
        
        $transaction = Transaction::where('stripe_payout_id', $payout->id)->first();
        
        if (!$transaction && !in_array($event->type, ['payout.created', 'payout.updated'])) {
            Log::error('Transaction not found for payout.', ['payout_id' => $payout->id]);
            return;
        }

        switch ($event->type) {
            case 'payout.created':
                Log::info('New payout created', [
                    'payout_id' => $payout->id,
                    'amount' => $payout->amount,
                    'currency' => $payout->currency,
                    'arrival_date' => $payout->arrival_date
                ]);
                break;

            case 'payout.updated':
                Log::info('Payout updated', [
                    'payout_id' => $payout->id,
                    'status' => $payout->status,
                    'metadata' => $payout->metadata
                ]);
                break;

            case 'payout.paid':
                if ($transaction) {
                    $transaction->status = Transaction::STATUS_COMPLETED;
                    Log::info('Payout successful', ['payout_id' => $payout->id]);
                }
                break;

            case 'payout.failed':
                if ($transaction) {
                    $this->handleFailedPayout($transaction, $payout);
                    Log::error('Payout failed', [
                        'payout_id' => $payout->id,
                        'failure_code' => $payout->failure_code,
                        'failure_message' => $payout->failure_message
                    ]);
                }
                break;

            case 'payout.canceled':
                if ($transaction) {
                    $this->handleCanceledPayout($transaction, $payout);
                    Log::info('Payout canceled', ['payout_id' => $payout->id]);
                }
                break;
        }

        if ($transaction) {
            $transaction->metadata = array_merge(
                $transaction->metadata ?? [],
                [
                    'webhook_event' => $event->type,
                    'stripe_status' => $payout->status,
                    'failure_code' => $payout->failure_code ?? null,
                    'failure_message' => $payout->failure_message ?? null,
                    'arrival_date' => $payout->arrival_date ?? null
                ]
            );
            
            $transaction->save();
        }
    }

    protected function handleFailedPayout(Transaction $transaction, $payout): void
    {
        $transaction->status = Transaction::STATUS_FAILED;
        
        // Refund the amount back to wallet
        $wallet = $transaction->wallet;
        $wallet->balance += $transaction->amount;
        $wallet->save();

        // Notify user about the failed payout
        $transaction->wallet->user->notify(new PayoutFailed($transaction, $payout->failure_message ?? 'Unknown error'));
    }

    protected function handleCanceledPayout(Transaction $transaction, $payout): void
    {
        $transaction->status = Transaction::STATUS_CANCELED;
        
        // Refund the amount back to wallet
        $wallet = $transaction->wallet;
        $wallet->balance += $transaction->amount;
        $wallet->save();

        // Notify user about the canceled payout
        $transaction->wallet->user->notify(new PayoutCanceled($transaction));
    }

    protected function handlePaymentIntentSucceeded($paymentIntent): void
    {
        $walletId = $paymentIntent->metadata['wallet_id'] ?? null;
        if (!$walletId) {
            Log::error('No wallet ID in payment intent metadata.');
            return;
        }

        $transaction = Transaction::where('stripe_payment_id', $paymentIntent->id)->first();
        if (!$transaction) {
            Log::error('Transaction not found for payment intent.', ['payment_intent_id' => $paymentIntent->id]);
            return;
        }

        if ($transaction->status !== Transaction::STATUS_COMPLETED) {
            $transaction->status = Transaction::STATUS_COMPLETED;
            $transaction->save();

            // Notify the user about successful payment
            $transaction->wallet->user->notify(new PaymentSuccessful($transaction));
        }
    }

    protected function handlePaymentIntentFailed($paymentIntent): void
    {
        $transaction = Transaction::where('stripe_payment_id', $paymentIntent->id)->first();
        if ($transaction) {
            $transaction->status = Transaction::STATUS_FAILED;
            $transaction->save();

            // Notify the user about failed payment
            $transaction->wallet->user->notify(new PaymentFailed($transaction));
        }
    }

    protected function handleTransferSucceeded($transfer): void
    {
        $transaction = Transaction::where('stripe_transfer_id', $transfer->id)->first();
        if ($transaction) {
            $transaction->status = Transaction::STATUS_COMPLETED;
            $transaction->save();

            // Notify the freelancer about successful transfer
            $transaction->wallet->user->notify(new PaymentReceived($transaction));
        }
    }

    protected function handleTransferFailed($transfer): void
    {
        $transaction = Transaction::where('stripe_transfer_id', $transfer->id)->first();
        if ($transaction) {
            $transaction->status = Transaction::STATUS_FAILED;
            $transaction->save();

            // Notify about failed transfer
            $transaction->wallet->user->notify(new PaymentFailed($transaction));
        }
    }


}
