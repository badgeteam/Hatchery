<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class VoteTest.
 *
 * @author annejan@badge.team
 */
class VoteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Assert the Vote is cast by a User.
     */
    public function testVoteUserRelationship(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Vote $vote */
        $vote = Vote::factory()->create();
        $this->assertInstanceOf(User::class, $vote->user);
        $this->assertEquals($user->id, $vote->user->id);
    }

    /**
     * Assert the Vote is cast on a Project.
     */
    public function testVoteProjectRelationship(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Vote $vote */
        $vote = Vote::factory()->create(['project_id' => $project->id]);
        $this->assertInstanceOf(Project::class, $vote->project);
        $this->assertEquals($project->id, $vote->project->id);
    }

    /**
     * Assert the Vote type is sane.
     */
    public function testVoteTypeEnumDefault(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Vote $vote */
        $vote = Vote::factory()->create();
        $this->assertNull($vote->type);
        /** @var Vote $vote */
        $vote = Vote::find($vote->id);
        $this->assertEquals('up', $vote->type);
    }

    /**
     * Assert the Vote type is sane.
     */
    public function testVoteTypeEnumPig(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Vote $vote */
        $vote = Vote::factory()->create(['project_id' => $project->id, 'type' => 'pig']);
        $this->assertEquals('pig', $vote->type);
        /** @var Vote $vote */
        $vote = Vote::find($vote->id);
        $this->assertEquals('pig', $vote->type);
    }

    /**
     * Assert the Vote type is sane.
     */
    public function testVoteTypeEnumDown(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Vote $vote */
        $vote = Vote::factory()->create(['project_id' => $project->id, 'type' => 'down']);
        $this->assertEquals('down', $vote->type);
        /** @var Vote $vote */
        $vote = Vote::find($vote->id);
        $this->assertEquals('down', $vote->type);
    }
}
