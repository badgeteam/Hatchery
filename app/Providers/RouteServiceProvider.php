<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Class RouteServiceProvider.
 *
 * @author annejan@badge.team
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/legacy.php'));
        Route::middleware('web')
            ->namespace($this->namespace)
            ->prefix('eggs')
            ->group(base_path('routes/eggs.php'));
        Route::middleware('web')
            ->namespace($this->namespace)
            ->prefix('basket')
            ->group(base_path('routes/basket.php'));
        Route::middleware('web')
            ->namespace($this->namespace)
            ->prefix('weather')
            ->group(base_path('routes/weather.php'));
        Route::middleware('web')
            ->namespace($this->namespace)
            ->prefix('mch2022')
            ->group(base_path('routes/mch2022.php'));
    }
}
