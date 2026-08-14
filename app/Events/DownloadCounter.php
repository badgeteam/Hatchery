<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Events;

use App\Models\Project;
use Illuminate\Queue\SerializesModels;

/**
 * Class DownloadCounter.
 *
 * @author annejan@badge.team
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
