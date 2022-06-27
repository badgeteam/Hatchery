<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\UpdateLicenses;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel.
 *
 * @author annejan@badge.team
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<string>
     */
    protected $commands = [
        GenerateSitemap::class,
        UpdateLicenses::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @codeCoverageIgnore
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sitemap:generate')->hourly();
    }
}
