<?php

namespace Tests\Feature\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\Event;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret;

    protected function setUp(): void
    {
        parent::setUp();
        $this->webhookSecret = 'whsec_test_secret';
        config(['stripe.webhook_secret' => $this->webhookSecret]);
    }

    private function generateTestSignature(string $payload): string
    {
        $timestamp = time();
        $signedPayload = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedPayload, $this->webhookSecret);
        
        return "t=$timestamp,v1=$signature";
    }

    public function test_webhook_requires_signature(): void
    {
        $response = $this->postJson('/api/stripe/webhook', [
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => []]
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_validates_signature(): void
    {
        $payload = json_encode([
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => []]
        ]);

        $response = $this->postJson('/api/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => 'invalid_signature'
        ]);

        $response->assertStatus(400);
    }

    public function test_handles_payment_intent_succeeded(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id
        ]);

        $transaction = Transaction::factory()->create([
            'wallet_id' => $wallet->id,
            'amount' => 100,
            'type' => Transaction::TYPE_DEPOSIT,
            'status' => Transaction::STATUS_PENDING,
            'stripe_payment_intent_id' => 'pi_test123'
        ]);

        $payload = json_encode([
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test123',
                    'amount' => 10000, // Amount in cents
                    'status' => 'succeeded'
                ]
            ]
        ]);

        $response = $this->postJson('/api/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => $this->generateTestSignature($payload)
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => Transaction::STATUS_COMPLETED
        ]);

        $wallet->refresh();
        $this->assertEquals(100, $wallet->balance);
    }

    public function test_handles_payment_intent_failed(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id
        ]);

        $transaction = Transaction::factory()->create([
            'wallet_id' => $wallet->id,
            'amount' => 100,
            'type' => Transaction::TYPE_DEPOSIT,
            'status' => Transaction::STATUS_PENDING,
            'stripe_payment_intent_id' => 'pi_test123'
        ]);

        $payload = json_encode([
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_test123',
                    'amount' => 10000,
                    'status' => 'failed'
                ]
            ]
        ]);

        $response = $this->postJson('/api/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => $this->generateTestSignature($payload)
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => Transaction::STATUS_FAILED
        ]);

        $wallet->refresh();
        $this->assertEquals(0, $wallet->balance);
    }

    public function test_handles_transfer_succeeded(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id
        ]);

        $transaction = Transaction::factory()->create([
            'wallet_id' => $wallet->id,
            'amount' => 100,
            'type' => Transaction::TYPE_ESCROW_RELEASE,
            'status' => Transaction::STATUS_PENDING,
            'stripe_transfer_id' => 'tr_test123'
        ]);

        $payload = json_encode([
            'type' => 'transfer.succeeded',
            'data' => [
                'object' => [
                    'id' => 'tr_test123',
                    'amount' => 10000,
                    'status' => 'succeeded'
                ]
            ]
        ]);

        $response = $this->postJson('/api/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => $this->generateTestSignature($payload)
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => Transaction::STATUS_COMPLETED
        ]);
    }

    public function test_handles_transfer_failed(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id
        ]);

        $transaction = Transaction::factory()->create([
            'wallet_id' => $wallet->id,
            'amount' => 100,
            'type' => Transaction::TYPE_ESCROW_RELEASE,
            'status' => Transaction::STATUS_PENDING,
            'stripe_transfer_id' => 'tr_test123'
        ]);

        $payload = json_encode([
            'type' => 'transfer.failed',
            'data' => [
                'object' => [
                    'id' => 'tr_test123',
                    'amount' => 10000,
                    'status' => 'failed'
                ]
            ]
        ]);

        $response = $this->postJson('/api/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => $this->generateTestSignature($payload)
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => Transaction::STATUS_FAILED
        ]);
    }

    public function test_ignores_unhandled_event_types(): void
    {
        $payload = json_encode([
            'type' => 'unhandled.event',
            'data' => ['object' => []]
        ]);

        $response = $this->postJson('/api/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => $this->generateTestSignature($payload)
        ]);

        $response->assertSuccessful();
    }
}
