<?php

namespace App\Providers;

use App\Models\Job;
use App\Models\User;
use App\Models\Proposal;
use App\Models\UserAward;
use App\Models\UserProject;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, function ($app) {
            return new StripeClient(config('services.stripe.secret'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'user'         => User::class,
            'user_project' => UserProject::class,
            'user_award'   => UserAward::class,
            'job'          => Job::class,
            'proposal'     => Proposal::class,
        ]);
    }
}
