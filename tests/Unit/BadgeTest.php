<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Badge;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class BadgeTest.
 *
 * @author annejan@badge.team
 */
class BadgeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Assert the Badge has a collection of Projects.
     */
    public function testBadgeProjectRelationship(): void
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
        $this->assertInstanceOf(Collection::class, $badge->projects);
        $this->assertInstanceOf(Project::class, $badge->projects->first());
    }
}
