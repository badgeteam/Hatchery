<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Project;

class ProjectTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Assert the Project has a relation with a single User
     */
    public function testProjectUserRelationship()
    {
        $project = factory(Project::class)->create();
        $this->assertInstanceOf('App\Models\User', $project->user);
    }

    /**
     * Assert the Project can have a collection of Versions
     */
    public function testProjectVersionRelationship()
    {
        $project = factory(Project::class)->create();
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $project->versions);
        $this->assertEmpty($project->versions);
    }
}
