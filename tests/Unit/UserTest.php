<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Project;

class UserTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Assert the User has a relation with zero or more Project(s)
     */
    public function testUserProjectsRelationship()
    {
        $user = factory(User::class)->create();
        $this->assertEmpty($user->projects);
        $project = factory(Project::class)->create(['user_id' => $user->id]);
        $user = User::find($user->id);
        $this->assertCount(1, $user->projects);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->projects);
        $this->assertInstanceOf('App\Models\Project', $user->projects->first());
        $this->assertEquals($project->id, $user->projects->first()->id);
        factory(Project::class)->create(['user_id' => $user->id]);
        $user = User::find($user->id);
        $this->assertCount(2, $user->projects);
    }
}
