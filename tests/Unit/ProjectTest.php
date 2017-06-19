<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Version;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Project;

class ProjectTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Assert the Project has a relation with a single User.
     */
    public function testProjectUserRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertInstanceOf(User::class, $project->user);
    }

    /**
     * Assert the Project can have a collection of Versions.
     */
    public function testProjectVersionRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertInstanceOf(Collection::class, $project->versions);
    }

    /**
     * Assert the Project always has at-least one Version.
     */
    public function testNewProjectHasAVersion()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertCount(1, $project->versions);
    }

    /**
     * Check if Project revision helper.
     */
    public function testProjectRevisionAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $this->assertNull($version->project->revision);
        $version->zip = 'iets';
        $version->save();
        $this->assertEquals("1", $version->project->revision);

    }
}
