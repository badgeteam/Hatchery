<?php

namespace Tests\Unit;

use App\Models\Badge;
use App\Models\BadgeProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class BadgeProjectTest.
 *
 * @author annejan@badge.team
 */
class BadgeProjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Assert the Badge has a collection of BadgeProjects.
     */
    public function testBadgeProjectBadgeRelationship(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var Project $project */
        $project = Project::factory()->create();
        $project->badges()->attach($badge);
        /** @var Badge $badge */
        $badge = Badge::find($badge->id);
        $this->assertInstanceOf(Collection::class, $badge->states);
        $this->assertInstanceOf(BadgeProject::class, $badge->states->first());
        $this->assertCount(1, $badge->states);
        /** @var BadgeProject $state */
        $state = $badge->states->first();
        $this->assertInstanceOf(Badge::class, $state->badge);
        $this->assertEquals($badge->id, $state->badge_id);
    }

    /**
     * Assert the Project has a collection of BadgeProjects.
     */
    public function testBadgeProjectProjectRelationship(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var Project $project */
        $project = Project::factory()->create();
        $project->badges()->attach($badge);
        /** @var BadgeProject $state */
        $state = $badge->states->first();
        $this->assertInstanceOf(Project::class, $state->project);
        $this->assertEquals($project->id, $state->project_id);
    }
}
