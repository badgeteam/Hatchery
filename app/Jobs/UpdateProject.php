<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Support\Helpers;
use Composer\Util\Git;
use Cz\Git\GitException;
use Cz\Git\GitRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateProject implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /** @var Project */
    private $project;
    /** @var User */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param Project $project
     * @param User    $user
     *
     * @throws GitException
     *
     * @return void
     */
    public function __construct(Project $project, User $user)
    {
        $this->project = $project;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @throws GitException
     *
     * @return void
     */
    public function handle()
    {
        $tempFolder = sys_get_temp_dir().'/'.$this->project->slug;
        if (!file_exists($tempFolder.'/.git/HEAD')) {
            $repo = GitRepository::cloneRepository($this->project->git, $tempFolder,
                ['-q', '--single-branch', '--depth', 1]);
        } else {
            $repo = new GitRepository($tempFolder);
        }

        if ($this->project->git_commit_id === $repo->getLastCommitId()) {

            return;
        }
        $this->project->git_commit_id = $repo->getLastCommitId();
        $this->project->save();
        $version = $this->project->getUnpublishedVersion();
        Helpers::addFiles($tempFolder, $version);
        PublishProject::dispatch($this->project, $this->user);
    }
}
