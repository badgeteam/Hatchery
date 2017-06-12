<?php

namespace Tests\Unit;

use App\Models\User;
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
        $project = factory(Project::class)->create();
        $this->assertInstanceOf(User::class, $project->user);
    }

    /**
     * Assert the Project can have a collection of Versions.
     */
    public function testProjectVersionRelationship()
    {
        $project = factory(Project::class)->create();
        $this->assertInstanceOf(Collection::class, $project->versions);
        $this->assertEmpty($project->versions);
    }
}
