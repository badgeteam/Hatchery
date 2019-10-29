<?php

namespace App\Providers;

use App\Policies\FilePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\UserPolicy;
use App\Policies\VotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Class AuthServiceProvider.
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Project' => ProjectPolicy::class,
        'App\Models\File'    => FilePolicy::class,
        'App\Models\User'    => UserPolicy::class,
        'App\Models\Vote'    => VotePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
