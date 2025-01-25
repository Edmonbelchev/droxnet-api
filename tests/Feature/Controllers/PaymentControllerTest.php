<?php

namespace Tests\Feature\Controllers;

use App\Models\Job;
use App\Models\Milestone;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Job $job;
    private Wallet $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->job = Job::factory()->create([
            'user_uuid' => $this->user->uuid,
            'status' => 'in_progress'
        ]);
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 1000.00
        ]);
    }

    public function test_deposit_requires_authentication(): void
    {
        $response = $this->postJson('/api/deposit', [
            'amount' => 100,
            'payment_method_id' => 'pm_test123'
        ]);

        $response->assertUnauthorized();
    }

    public function test_deposit_validates_input(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/deposit', [
                'amount' => 'invalid',
                'payment_method_id' => ''
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'payment_method_id']);
    }

    public function test_deposit_creates_transaction(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/deposit', [
                'amount' => 100,
                'payment_method_id' => 'pm_test123'
            ]);

        $response->assertSuccessful()
            ->assertJson([
                'message' => 'Deposit successful',
                'transaction' => [
                    'amount' => 100,
                    'type' => Transaction::TYPE_DEPOSIT,
                    'status' => Transaction::STATUS_COMPLETED
                ]
            ]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $this->wallet->id,
            'amount' => 100,
            'type' => Transaction::TYPE_DEPOSIT
        ]);
    }

    public function test_create_milestone_requires_authentication(): void
    {
        $response = $this->postJson("/api/jobs/{$this->job->id}/milestones", [
            'title' => 'Test Milestone',
            'amount' => 100
        ]);

        $response->assertUnauthorized();
    }

    public function test_create_milestone_validates_input(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/jobs/{$this->job->id}/milestones", [
                'title' => '',
                'amount' => 'invalid'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'amount']);
    }

    public function test_create_milestone_successful(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/jobs/{$this->job->id}/milestones", [
                'title' => 'Test Milestone',
                'description' => 'Test Description',
                'amount' => 100
            ]);

        $response->assertSuccessful()
            ->assertJson([
                'message' => 'Milestone created successfully',
                'milestone' => [
                    'title' => 'Test Milestone',
                    'amount' => 100,
                    'status' => Milestone::STATUS_PENDING
                ]
            ]);

        $this->assertDatabaseHas('milestones', [
            'job_id' => $this->job->id,
            'title' => 'Test Milestone',
            'amount' => 100
        ]);
    }

    public function test_fund_milestone_requires_authentication(): void
    {
        $milestone = Milestone::factory()->create([
            'job_id' => $this->job->id
        ]);

        $response = $this->postJson("/api/milestones/{$milestone->id}/fund");

        $response->assertUnauthorized();
    }

    public function test_fund_milestone_requires_authorization(): void
    {
        $otherUser = User::factory()->create();
        $milestone = Milestone::factory()->create([
            'job_id' => $this->job->id
        ]);

        $response = $this->actingAs($otherUser)
            ->postJson("/api/milestones/{$milestone->id}/fund");

        $response->assertForbidden();
    }

    public function test_fund_milestone_successful(): void
    {
        $milestone = Milestone::factory()->create([
            'job_id' => $this->job->id,
            'amount' => 100,
            'status' => Milestone::STATUS_PENDING
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/milestones/{$milestone->id}/fund");

        $response->assertSuccessful()
            ->assertJson([
                'message' => 'Milestone funded successfully',
                'transaction' => [
                    'amount' => 100,
                    'type' => Transaction::TYPE_ESCROW_HOLD,
                    'status' => Transaction::STATUS_COMPLETED
                ]
            ]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $this->wallet->id,
            'amount' => 100,
            'type' => Transaction::TYPE_ESCROW_HOLD
        ]);

        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'status' => Milestone::STATUS_FUNDED
        ]);
    }

    public function test_release_milestone_payment_requires_authentication(): void
    {
        $milestone = Milestone::factory()->create([
            'job_id' => $this->job->id
        ]);

        $response = $this->postJson("/api/milestones/{$milestone->id}/release");

        $response->assertUnauthorized();
    }

    public function test_release_milestone_payment_requires_authorization(): void
    {
        $otherUser = User::factory()->create();
        $milestone = Milestone::factory()->create([
            'job_id' => $this->job->id
        ]);

        $response = $this->actingAs($otherUser)
            ->postJson("/api/milestones/{$milestone->id}/release");

        $response->assertForbidden();
    }

    public function test_release_milestone_payment_successful(): void
    {
        $freelancer = User::factory()->create();
        $freelancerWallet = Wallet::factory()->create([
            'user_id' => $freelancer->id,
            'stripe_connect_id' => 'acct_test123'
        ]);

        $milestone = Milestone::factory()->create([
            'job_id' => $this->job->id,
            'amount' => 100,
            'status' => Milestone::STATUS_COMPLETED
        ]);

        $this->wallet->update([
            'escrow_balance' => 100
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/milestones/{$milestone->id}/release");

        $response->assertSuccessful()
            ->assertJson([
                'message' => 'Payment released successfully',
                'transaction' => [
                    'amount' => 100,
                    'type' => Transaction::TYPE_ESCROW_RELEASE,
                    'status' => Transaction::STATUS_COMPLETED
                ]
            ]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $this->wallet->id,
            'amount' => 100,
            'type' => Transaction::TYPE_ESCROW_RELEASE
        ]);

        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'status' => Milestone::STATUS_RELEASED
        ]);

        $this->wallet->refresh();
        $this->assertEquals(0, $this->wallet->escrow_balance);

        $freelancerWallet->refresh();
        $this->assertEquals(90, $freelancerWallet->balance); // After 10% platform fee
    }
}
