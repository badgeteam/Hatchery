<?php

namespace App\Listeners;

use App\Events\DownloadCounter;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

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
     * @param  DownloadCounter  $event
     * @return void
     */
    public function handle(DownloadCounter $event)
    {
	Log::error('Incrementing download counter for project: ' . $event->project->slug);
	// Increment the counter by one
        $event->project->increment('download_counter');

	// Increment the counter on the model, because the increment function doesn't
	$event->project->download_counter+=1;
    }
}
