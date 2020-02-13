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

/**
 * Class BadgeProjectTest.
 *
 * @author annejan@badge.team
 */
class BadgeProjectTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    /**
     * Assert the Badge has a collection of BadgeProjects.
     */
    public function testBadgeProjectBadgeRelationship(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $badge = factory(Badge::class)->create();
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $project->badges()->attach($badge);
        /** @var Badge $badge */
        $badge = Badge::find($badge->id);
        $this->assertInstanceOf(Collection::class, $badge->states);
        $this->assertInstanceOf(BadgeProject::class, $badge->states->first());
        $this->assertCount(1, $badge->states);
        /** @var BadgeProject $state */
        $state = $badge->states->first();
        $this->assertInstanceOf(Badge::class, $state->badge);
        $this->assertEquals($badge->id, $state->badge->id);
    }

    /**
     * Assert the Project has a collection of BadgeProjects.
     */
    public function testBadgeProjectProjectRelationship(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = factory(Badge::class)->create();
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $project->badges()->attach($badge);
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertInstanceOf(Collection::class, $project->badges);
        $this->assertInstanceOf(Badge::class, $project->badges->first());
        $this->assertInstanceOf(Collection::class, $project->states);
        $this->assertInstanceOf(BadgeProject::class, $project->states->first());
        $this->assertCount(1, $project->states);
        /** @var BadgeProject $state */
        $state = $badge->states->first();
        $this->assertInstanceOf(Badge::class, $state->badge);
        $this->assertEquals($badge->id, $state->badge->id);
    }
}
