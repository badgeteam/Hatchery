<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Models\Project;

class DownloadCounter
{
    use SerializesModels;

    public $project;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

}
