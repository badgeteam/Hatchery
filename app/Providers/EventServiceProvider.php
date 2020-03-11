<?php

namespace App\Providers;

use App\Events\DownloadCounter;
use App\Listeners\DownloadCounterListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider.
 *
 * @author annejan@badge.team
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<array>
     */
    protected $listen = [
        DownloadCounter::class => [
            DownloadCounterListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
