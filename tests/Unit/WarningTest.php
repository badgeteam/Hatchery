<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Models\Warning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class WarningTest.
 *
 * @author annejan@badge.team
 */
class WarningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Assert the Warning is cast by a User.
     */
    public function testWarningUserRelationship(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Warning $warning */
        $warning = Warning::factory()->create();
        $this->assertInstanceOf(User::class, $warning->user);
        $this->assertEquals($user->id, $warning->user->id);
    }

    /**
     * Assert the Warning is cast on a Project.
     */
    public function testWarningProjectRelationship(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Warning $warning */
        $warning = Warning::factory()->create(['project_id' => $project->id]);
        $this->assertInstanceOf(Project::class, $warning->project);
        $this->assertEquals($project->id, $warning->project->id);
    }
}
