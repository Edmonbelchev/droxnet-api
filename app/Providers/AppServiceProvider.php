<?php

namespace App\Providers;

use App\Models\UserAward;
use App\Models\UserProject;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'user_project' => UserProject::class,
            'user_award'   => UserAward::class,
        ]);
    }
}
