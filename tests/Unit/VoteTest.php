<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    /**
     * Assert the Vote is cast by a User.
     */
    public function testVoteUserRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $vote = factory(Vote::class)->create();
        $this->assertInstanceOf(User::class, $vote->user);
        $this->assertEquals($user->id, $vote->user->id);
    }

    /**
     * Assert the Vote is cast on a Project.
     */
    public function testVoteProjectRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $vote = factory(Vote::class)->create(['project_id' => $project->id]);
        $this->assertInstanceOf(Project::class, $vote->project);
        $this->assertEquals($project->id, $vote->project->id);
    }

    /**
     * Assert the Vote type is sane.
     */
    public function testVoteTypeEnumDefault()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $vote = factory(Vote::class)->create();
        $this->assertNull($vote->type);
        $vote = Vote::find($vote->id);
        $this->assertEquals('up', $vote->type);
    }

    /**
     * Assert the Vote type is sane.
     */
    public function testVoteTypeEnumPig()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $vote = factory(Vote::class)->create(['project_id' => $project->id, 'type' => 'pig']);
        $this->assertEquals('pig', $vote->type);
        $vote = Vote::find($vote->id);
        $this->assertEquals('pig', $vote->type);
    }

    /**
     * Assert the Vote type is sane.
     */
    public function testVoteTypeEnumDown()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $vote = factory(Vote::class)->create(['project_id' => $project->id, 'type' => 'down']);
        $this->assertEquals('down', $vote->type);
        $vote = Vote::find($vote->id);
        $this->assertEquals('down', $vote->type);
    }

//    /**
//     * Assert the Vote type is sane.
//     */
//    public function testVoteTypeEnumIllegal()
//    {
//        $this->expectException(QueryException::class);
//        $user = factory(User::class)->create();
//        $this->be($user);
//        $project = factory(Project::class)->create();
//        factory(Vote::class)->create(['project_id' => $project->id, 'type' => 'broken']);
//    }
}
