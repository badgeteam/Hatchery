<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\File;
use App\Policies\ProjectPolicy;
use App\Policies\FilePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
	'App\Models\Project' => 'App\Policies\ProjectPolicy',
	'App\Models\File' => 'App\Policies\FilePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
