<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Badge;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Vote;
use App\Policies\BadgePolicy;
use App\Policies\FilePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\UserPolicy;
use App\Policies\VotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Class AuthServiceProvider.
 *
 * @author annejan@badge.team
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        File::class    => FilePolicy::class,
        User::class    => UserPolicy::class,
        Vote::class    => VotePolicy::class,
        Badge::class   => BadgePolicy::class,
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
