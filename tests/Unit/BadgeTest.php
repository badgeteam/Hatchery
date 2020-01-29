<?php

namespace Tests\Unit;

use App\Models\Badge;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class BadgeTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    /**
     * Assert the Badge has a collection of Projects.
     */
    public function testBadgeProjectRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $badge = factory(Badge::class)->create();
        $project = factory(Project::class)->create();
        $project->badges()->attach($badge);

        $badge = Badge::find($badge->id);
        $this->assertInstanceOf(Collection::class, $badge->projects);
        $this->assertInstanceOf(Project::class, $badge->projects->first());
    }
}
