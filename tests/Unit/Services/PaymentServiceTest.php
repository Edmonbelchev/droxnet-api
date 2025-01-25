<?php

namespace Tests\Unit\Services;

use App\Models\Job;
use App\Models\Milestone;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PaymentService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\StripeClient;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;
    private StripeClient $stripeMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Stripe client
        $this->stripeMock = $this->createMock(StripeClient::class);
        $this->stripeMock->customers = $this->createMock(\Stripe\Service\CustomerService::class);
        $this->stripeMock->accounts = $this->createMock(\Stripe\Service\AccountService::class);
        $this->stripeMock->paymentIntents = $this->createMock(\Stripe\Service\PaymentIntentService::class);
        $this->stripeMock->transfers = $this->createMock(\Stripe\Service\TransferService::class);
        
        $this->paymentService = new PaymentService($this->stripeMock);
    }

    public function test_create_or_get_wallet(): void
    {
        $user = User::factory()->create();
        
        $wallet = $this->paymentService->createOrGetWallet($user);
        
        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals($user->uuid, $wallet->user_uuid);
        $this->assertEquals(0, $wallet->balance);
        $this->assertEquals(0, $wallet->escrow_balance);
        $this->assertEquals('USD', $wallet->currency);
    }

    public function test_get_existing_wallet(): void
    {
        $user = User::factory()->create();
        $existingWallet = Wallet::factory()->create([
            'user_uuid' => $user->uuid,
            'balance' => 100.00
        ]);
        
        $wallet = $this->paymentService->createOrGetWallet($user);
        
        $this->assertEquals($existingWallet->id, $wallet->id);
        $this->assertEquals($user->uuid, $wallet->user_uuid);
        $this->assertEquals(100.00, $wallet->balance);
    }

    public function test_deposit_to_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_uuid' => $user->uuid,
            'balance' => 100.00
        ]);
        
        $transaction = $this->paymentService->depositToWallet($wallet, 50.00);
        
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals(50.00, $transaction->amount);
        $this->assertEquals(Transaction::TYPE_DEPOSIT, $transaction->type);
        
        $wallet->refresh();
        $this->assertEquals(150.00, $wallet->balance);
    }

    public function test_create_milestone(): void
    {
        $employer = User::factory()->create();
        $freelancer = User::factory()->create();
        
        $job = Job::factory()->create([
            'user_uuid' => $employer->uuid,
            'status' => 'ongoing'
        ]);
        
        $proposal = Proposal::factory()->create([
            'job_id' => $job->id,
            'user_uuid' => $freelancer->uuid,
            'status' => 'accepted'
        ]);
        
        $milestone = $this->paymentService->createMilestone($job, [
            'title' => 'Test Milestone',
            'description' => 'Test Description',
            'amount' => 100.00,
            'due_date' => now()->addDays(7),
            'proposal_id' => $proposal->id
        ]);
        
        $this->assertInstanceOf(Milestone::class, $milestone);
        $this->assertEquals($job->id, $milestone->job_id);
        $this->assertEquals($proposal->id, $milestone->proposal_id);
        $this->assertEquals('Test Milestone', $milestone->title);
        $this->assertEquals(100.00, $milestone->amount);
        $this->assertEquals(Milestone::STATUS_PENDING, $milestone->status);
    }

    public function test_fund_milestone(): void
    {
        $employer = User::factory()->create();
        $freelancer = User::factory()->create();
        
        $job = Job::factory()->create([
            'user_uuid' => $employer->uuid,
            'status' => 'ongoing'
        ]);
        
        $proposal = Proposal::factory()->create([
            'job_id' => $job->id,
            'user_uuid' => $freelancer->uuid,
            'status' => 'accepted'
        ]);

        $milestone = Milestone::factory()->create([
            'job_id' => $job->id,
            'proposal_id' => $proposal->id,
            'amount' => 1000,
            'status' => Milestone::STATUS_PENDING
        ]);

        $wallet = Wallet::factory()->create([
            'user_uuid' => $employer->uuid,
            'balance' => 2000
        ]);

        $transaction = $this->paymentService->fundMilestone($milestone);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $wallet->id,
            'amount' => 1000,
            'type' => Transaction::TYPE_ESCROW_HOLD,
            'status' => Transaction::STATUS_COMPLETED
        ]);

        $milestone->refresh();
        $this->assertEquals(Milestone::STATUS_FUNDED, $milestone->status);
        
        $wallet->refresh();
        $this->assertEquals(1000, $wallet->balance);
        $this->assertEquals(1000, $wallet->escrow_balance);
    }

    public function test_fund_milestone_with_insufficient_balance(): void
    {
        $employer = User::factory()->create();
        $freelancer = User::factory()->create();
        
        $job = Job::factory()->create([
            'user_uuid' => $employer->uuid,
            'status' => 'ongoing'
        ]);
        
        $proposal = Proposal::factory()->create([
            'job_id' => $job->id,
            'user_uuid' => $freelancer->uuid,
            'status' => 'accepted'
        ]);

        $milestone = Milestone::factory()->create([
            'job_id' => $job->id,
            'proposal_id' => $proposal->id,
            'amount' => 100.00,
            'status' => Milestone::STATUS_PENDING
        ]);

        $wallet = Wallet::factory()->create([
            'user_uuid' => $employer->uuid,
            'balance' => 50.00
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insufficient funds in wallet');

        $this->paymentService->fundMilestone($milestone);
    }

    public function test_release_milestone_payment(): void
    {
        $employer = User::factory()->create();
        $freelancer = User::factory()->create();
        
        $job = Job::factory()->create([
            'user_uuid' => $employer->uuid,
            'status' => 'ongoing'
        ]);
        
        $proposal = Proposal::factory()->create([
            'job_id' => $job->id,
            'user_uuid' => $freelancer->uuid,
            'status' => 'accepted'
        ]);

        $milestone = Milestone::factory()->create([
            'job_id' => $job->id,
            'proposal_id' => $proposal->id,
            'amount' => 100.00,
            'status' => Milestone::STATUS_FUNDED
        ]);

        $employerWallet = Wallet::factory()->create([
            'user_uuid' => $employer->uuid,
            'escrow_balance' => 100.00
        ]);

        $freelancerWallet = Wallet::factory()->create([
            'user_uuid' => $freelancer->uuid,
            'balance' => 0
        ]);

        $this->stripeMock->transfers->expects($this->once())
            ->method('create')
            ->willReturn((object)[
                'id' => 'tr_test123'
            ]);

        $transaction = $this->paymentService->releaseMilestonePayment($milestone);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals(Transaction::TYPE_ESCROW_RELEASE, $transaction->type);
        $this->assertEquals(Transaction::STATUS_COMPLETED, $transaction->status);
        
        $milestone->refresh();
        $this->assertEquals(Milestone::STATUS_RELEASED, $milestone->status);
        
        $employerWallet->refresh();
        $this->assertEquals(0, $employerWallet->escrow_balance);
        
        $freelancerWallet->refresh();
        $this->assertEquals(90.00, $freelancerWallet->balance); // 100 - 10% platform fee
    }
}
