<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Queue\SerializesModels;

/**
 * Class DownloadCounter
 * @author annejan@badge.team
 * @package App\Events
 */
class DownloadCounter
{
    use SerializesModels;

    /**
     * @var Project
     */
    public $project;

    /**
     * DownloadCounter constructor.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }
}
