<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Support\Helpers;
use Cz\Git\GitRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportProject implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /** @var Project */
    private $project;
    /** @var User */
    private $user;
    /** @var GitRepository */
    private $repo;
    /** @var string */
    private $tempFolder;

    /**
     * Create a new job instance.
     *
     * @param Project       $project
     * @param User          $user
     * @param GitRepository $repo
     * @param string        $tempFolder
     *
     * @return void
     */
    public function __construct(Project $project, User $user, GitRepository $repo, string $tempFolder)
    {
        $this->project = $project;
        $this->user = $user;
        $this->repo = $repo;
        $this->tempFolder = $tempFolder;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle()
    {
        /** @var Version $version */
        $version = $this->project->versions->last();
        foreach ($version->files as $file) {
            // Clean out magical empty __init__.py
            $file->delete();
        }
        Helpers::addFiles($this->tempFolder, $version);
        Helpers::delTree($this->tempFolder);
        PublishProject::dispatch($this->project, $this->user);
    }
}
