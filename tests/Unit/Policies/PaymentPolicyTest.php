<?php

namespace Tests\Unit\Policies;

use App\Models\Job;
use App\Models\Milestone;
use App\Models\User;
use App\Policies\PaymentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PaymentPolicy $policy;
    private User $employer;
    private User $freelancer;
    private User $otherUser;
    private Job $job;
    private Milestone $milestone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new PaymentPolicy();
        
        $this->employer = User::factory()->create();
        $this->freelancer = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        $this->job = Job::factory()->create([
            'user_uuid' => $this->employer->uuid,
            'freelancer_id' => $this->freelancer->id
        ]);
        
        $this->milestone = Milestone::factory()->create([
            'job_id' => $this->job->id
        ]);
    }

    public function test_employer_can_create_milestone(): void
    {
        $this->assertTrue(
            $this->policy->createMilestone($this->employer, $this->job)
        );
    }

    public function test_freelancer_cannot_create_milestone(): void
    {
        $this->assertFalse(
            $this->policy->createMilestone($this->freelancer, $this->job)
        );
    }

    public function test_other_user_cannot_create_milestone(): void
    {
        $this->assertFalse(
            $this->policy->createMilestone($this->otherUser, $this->job)
        );
    }

    public function test_employer_can_fund_milestone(): void
    {
        $this->assertTrue(
            $this->policy->fundMilestone($this->employer, $this->milestone)
        );
    }

    public function test_freelancer_cannot_fund_milestone(): void
    {
        $this->assertFalse(
            $this->policy->fundMilestone($this->freelancer, $this->milestone)
        );
    }

    public function test_other_user_cannot_fund_milestone(): void
    {
        $this->assertFalse(
            $this->policy->fundMilestone($this->otherUser, $this->milestone)
        );
    }

    public function test_employer_can_release_payment(): void
    {
        $this->milestone->status = Milestone::STATUS_COMPLETED;
        
        $this->assertTrue(
            $this->policy->releasePayment($this->employer, $this->milestone)
        );
    }

    public function test_employer_cannot_release_uncompleted_milestone(): void
    {
        $this->milestone->status = Milestone::STATUS_FUNDED;
        
        $this->assertFalse(
            $this->policy->releasePayment($this->employer, $this->milestone)
        );
    }

    public function test_freelancer_cannot_release_payment(): void
    {
        $this->milestone->status = Milestone::STATUS_COMPLETED;
        
        $this->assertFalse(
            $this->policy->releasePayment($this->freelancer, $this->milestone)
        );
    }

    public function test_other_user_cannot_release_payment(): void
    {
        $this->milestone->status = Milestone::STATUS_COMPLETED;
        
        $this->assertFalse(
            $this->policy->releasePayment($this->otherUser, $this->milestone)
        );
    }
}
