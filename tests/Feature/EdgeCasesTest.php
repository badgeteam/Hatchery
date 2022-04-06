<?php

namespace Tests\Feature;

use App\Events\ProjectUpdated;
use App\Http\Controllers\BadgesController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VotesController;
use App\Http\Requests\FileUpdateRequest;
use App\Jobs\PublishProject;
use App\Jobs\UpdateProject;
use App\Models\Badge;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Models\Vote;
use App\Support\GitRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class EdgeCasesTest.
 *
 * These are edge cases to be handled.
 *
 * @author annejan@badge.team
 */
class EdgeCasesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Check that a user can delete user.
     */
    public function testUserDeleteRaceCondition(): void
    {
        $user = User::factory()->create();
        $this->be($user);

        $user = $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('delete')->once()->andThrow(new \Exception('b0rk'));
        })->makePartial();

        $usersController = new UsersController();
        $redirectResponse = $usersController->destroy($user);
        $this->assertEquals('[["b0rk"]]', (string) $redirectResponse->getSession()->get('errors'));
    }

    /**
     * Check that a user can delete Vote.
     */
    public function testProjectsVoteDeleteRaceCondition(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $project = Project::factory()->create();
        $vote = Vote::factory()->create([
            'project_id' => $project->id,
            'type'       => 'pig',
        ]);
        $project = $vote->project();

        $vote = $this->mock(Vote::class, function ($mock) use ($project) {
            $mock->shouldReceive('project')->once()->andReturn($project);
            $mock->shouldReceive('delete')->once()->andThrow(new \Exception('b0rk'));
        })->makePartial();

        $votesController = new VotesController();
        $redirectResponse = $votesController->destroy($vote);
        $this->assertEquals('[["b0rk"]]', (string) $redirectResponse->getSession()->get('errors'));
    }

    /**
     * Check that a user can delete Vote.
     */
    public function testProjectsDeleteRaceCondition(): void
    {
        $project = $this->mock(Project::class, function ($mock) {
            $mock->shouldReceive('save')->once();
            $mock->shouldReceive('delete')->once()->andThrow(new \Exception('b0rk'));
        })->makePartial();

        $projectsController = new ProjectsController();
        $redirectResponse = $projectsController->destroy($project);
        $this->assertEquals('[["b0rk"]]', (string) $redirectResponse->getSession()->get('errors'));
    }

    /**
     * Check that a user can delete File.
     */
    public function testFilesDeleteRaceCondition(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $version = Version::factory()->create();
        $file = $this->mock(File::class, function ($mock) use ($version) {
            $mock->shouldReceive('delete')->once()->andThrow(new \Exception('b0rk'));
            $mock->shouldReceive('getAttribute')->with('version')->once()->andReturn($version);
        })->makePartial();

        $filesController = new FilesController();
        $redirectResponse = $filesController->destroy($file);
        $this->assertEquals('[["b0rk"]]', (string) $redirectResponse->getSession()->get('errors'));
    }

    /**
     * Check that a user can delete File.
     */
    public function testFilesUpdateRaceCondition(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $file = $this->mock(File::class, function ($mock) {
            $mock->shouldReceive('save')->once()->andThrow(new \Exception('b0rk'));
            $mock->shouldReceive('getAttribute')->with('id')->once()->andReturn(1);
        })->makePartial();

        $request = new FileUpdateRequest();
        $filesController = new FilesController();
        $redirectResponse = $filesController->update($request, $file);
        $this->assertEquals('[["b0rk"]]', (string) $redirectResponse->getSession()->get('errors'));
    }

    /**
     * Magical errors in async jobs.
     */
    public function testPublishProjectException(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $version = Version::factory()->create();

        $project = $this->mock(Project::class, function ($mock) use ($version) {
            $mock->shouldReceive('getUnpublishedVersion')->once()->andReturn($version);
        })->makePartial();

        $publishProject = new PublishProject($project, $user);
        $publishProject->handle();
    }

    /**
     * Magical errors in async jobs.
     */
    public function testUpdateProjectException(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $version = Version::factory()->create();

        $project = $this->mock(Project::class, function ($mock) use ($version) {
            $mock->shouldReceive('getUnpublishedVersion')->once()->andReturn($version);
        })->makePartial();

        Event::fake();

        $publishProject = new UpdateProject($project, $user);
        $publishProject->handle(new GitRepository());

        Event::assertDispatched(ProjectUpdated::class, function ($e) {
            $this->assertEquals('danger', $e->type);

            return true;
        });
    }

    /**
     * Check that a user can delete Vote.
     */
    public function testBadgesDeleteRaceCondition(): void
    {
        $badge = $this->mock(Badge::class, function ($mock) {
            $mock->shouldReceive('delete')->once()->andThrow(new \Exception('b0rk'));
        })->makePartial();

        $projectsController = new BadgesController();
        $redirectResponse = $projectsController->destroy($badge);
        $this->assertEquals('[["b0rk"]]', (string) $redirectResponse->getSession()->get('errors'));
    }
}
