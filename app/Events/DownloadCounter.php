<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Queue\SerializesModels;

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
