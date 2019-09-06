<?php

namespace App\Listeners;

use App\Events\DownloadCounter;

/**
 * Class DownloadCounterListner.
 */
class DownloadCounterListner
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param DownloadCounter $event
     *
     * @return void
     */
    public function handle(DownloadCounter $event)
    {
        // Increment the counter by one
        $event->project->increment('download_counter');

        // Increment the counter on the model, because the increment function doesn't
        $event->project->download_counter += 1;
    }
}
