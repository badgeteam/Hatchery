<?php

namespace Tests\Feature;

use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VotesController;
use App\Models\Project;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

        $user = $this->mock(User::class, function ($mock)  {
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
        $project = $this->mock(Project::class, function ($mock)  {
            $mock->shouldReceive('save')->once();
            $mock->shouldReceive('delete')->once()->andThrow(new \Exception('b0rk'));
        })->makePartial();

        $projectsController = new ProjectsController();
        $redirectResponse = $projectsController->destroy($project);
        $this->assertEquals('[["b0rk"]]', (string) $redirectResponse->getSession()->get('errors'));
    }
}