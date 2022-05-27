<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\ProjectUpdated;
use App\Models\Project;
use App\Models\User;
use App\Support\Helpers;
use CzProject\GitPhp\Git;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class UpdateProject.
 *
 * @author annejan@badge.team
 */
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
     * @param Git $git
     *
     * @return void
     */
    public function handle(Git $git)
    {
        $version = $this->project->getUnpublishedVersion();

        try {
            $tempFolder = sys_get_temp_dir() . '/' . $this->project->slug;
            if (!file_exists($tempFolder . '/.git/HEAD')) {
                $repo = $git->cloneRepository(
                    (string)$this->project->git,
                    $tempFolder,
                    ['-q', '--single-branch', '--depth', 1]
                );
            } else {
                $repo = $git->open($tempFolder);
                $repo->pull();
            }

            if ($this->project->git_commit_id === $repo->getLastCommitId()->toString()) {
                event(new ProjectUpdated(
                    $version->project,
                    'Project ' . $version->project->name . ' was already up to date!',
                    'info'
                ));

                return;
            }
            $this->project->git_commit_id = $repo->getLastCommitId()->toString();
            $this->project->save();
            Helpers::addFiles($tempFolder, $version);
            PublishProject::dispatch($this->project, $this->user);
            event(new ProjectUpdated(
                $version->project,
                'Project ' . $version->project->name . ' updated successfully!'
            ));
        } catch (\Throwable $exception) {
            event(new ProjectUpdated($version->project, $exception->getMessage(), 'danger'));
        }
    }
}
