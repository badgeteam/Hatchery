<?php

namespace Tests\Unit;

use App\Models\Badge;
use App\Models\BadgeProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class BadgeProjectTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    /**
     * Assert the Badge has a collection of BadgeProjects.
     */
    public function testBadgeProjectBadgeRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $badge = factory(Badge::class)->create();
        $project = factory(Project::class)->create();
        $project->badges()->attach($badge);

        $badge = Badge::find($badge->id);
        $this->assertInstanceOf(Collection::class, $badge->states);
        $this->assertInstanceOf(BadgeProject::class, $badge->states->first());
        $this->assertCount(1, $badge->states);
        $this->assertInstanceOf(Badge::class, $badge->states->first()->badge);
        $this->assertEquals($badge->id, $badge->states->first()->badge->id);
    }

    /**
     * Assert the Project has a collection of BadgeProjects.
     */
    public function testBadgeProjectProjectRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $badge = factory(Badge::class)->create();
        $project = factory(Project::class)->create();
        $project->badges()->attach($badge);

        $project = Project::find($project->id);
        $this->assertInstanceOf(Collection::class, $project->badges);
        $this->assertInstanceOf(Badge::class, $project->badges->first());
        $this->assertInstanceOf(Collection::class, $project->states);
        $this->assertInstanceOf(BadgeProject::class, $project->states->first());
        $this->assertCount(1, $project->states);
        $this->assertInstanceOf(Badge::class, $project->states->first()->badge);
        $this->assertEquals($badge->id, $project->states->first()->badge->id);
    }
}
