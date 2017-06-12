<?php

namespace Tests\Unit;

use App\Models\Version;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Project;

class VersionTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Assert the Version has a relation with a single Project
     */
    public function testVersionProjectRelationship()
    {
        $version = factory(Version::class)->create();
        $this->assertInstanceOf(Project::class, $version->project);
    }
}
