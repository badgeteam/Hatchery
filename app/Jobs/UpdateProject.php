<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Support\Helpers;
use Cz\Git\GitException;
use Cz\Git\GitRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * @param Project $project
     * @param User $user
     * @param GitRepository|null $repo
     * @param string|null $tempFolder
     *
     * @return void
     * @throws GitException
     */
    public function __construct(Project $project, User $user, GitRepository $repo = null, string $tempFolder = null)
    {
        if ($tempFolder === null) {
            $tempFolder = sys_get_temp_dir().'/'.$project->slug;
        }
        if ($repo === null) {
            $repo = GitRepository::cloneRepository($project->git, $tempFolder,
                ['-q', '--single-branch', '--depth', 1]);
        }
        $this->project = $project;
        $this->user = $user;
        $this->repo = $repo;
        $this->tempFolder = $tempFolder;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GitException
     */
    public function handle()
    {
        if ($this->project->git_commit_id === $this->repo->getLastCommitId()) {
            Helpers::delTree($this->tempFolder);
            return;
        }
        $this->project->git_commit_id = $this->repo->getLastCommitId();
        $this->project->save();
        $version = $this->project->getUnpublishedVersion();
        $this->addFiles($this->tempFolder, $version);
        Helpers::delTree($this->tempFolder);
        PublishProject::dispatch($this->project, $this->user);
    }

    /**
     * @param string  $dir
     * @param Version $version
     * @param string  $prefix
     *
     * @return void
     */
    private function addFiles(string $dir, Version $version, $prefix = ''): void
    {
        $objects = scandir($dir);
        if (!$objects) {
            return;
        }
        $objects = array_diff($objects, ['.git', '.', '..']);
        foreach ($objects as $object) {
            if (is_dir("$dir/$object")) {
                $this->addFiles("$dir/$object", $version, "$prefix$object/");
            } else {
                if (File::valid($object)) {
                    $file = new File();
                    $file->user_id = $this->user->id;
                    $file->name = "$prefix$object";
                    $file->content = file_get_contents("$dir/$object");
                    $file->version()->associate($version);
                    $file->save();
                }
            }
        }
    }
}
