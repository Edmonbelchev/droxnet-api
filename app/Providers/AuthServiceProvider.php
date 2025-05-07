<?php

namespace App\Providers;

use App\Models\Job;
use App\Models\Milestone;
use App\Models\Proposal;
use App\Policies\JobPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ProposalPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Job::class => JobPolicy::class,
        Proposal::class => ProposalPolicy::class,
        Milestone::class => PaymentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
