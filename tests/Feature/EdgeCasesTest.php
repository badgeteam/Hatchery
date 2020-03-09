<?php

namespace Tests\Feature;

use App\Http\Controllers\FilesController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VotesController;
use App\Http\Requests\FileUpdateRequest;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
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
        $user = factory(User::class)->create();
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
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $vote = factory(Vote::class)->create([
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
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
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
        $user = factory(User::class)->create();
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
}
