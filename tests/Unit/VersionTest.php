<?php

namespace Tests\Unit;

use App\Models\Version;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Project;

class VersionTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Assert the Version has a relation with a single Project.
     */
    public function testVersionProjectRelationship()
    {
        $version = factory(Version::class)->create();
        $this->assertInstanceOf(Project::class, $version->project);
    }

    /**
     * Assert the Version can have a collection of Files.
     */
    public function testVersionFileRelationship()
    {
        $version = factory(Version::class)->create();
        $this->assertInstanceOf(Collection::class, $version->files);
        $this->assertEmpty($version->files);
    }

    public function testVersionPublishedHelper()
    {
        $version = factory(Version::class)->create();
        $this->assertFalse($version->published);
        $version->zip = 'iets';
        $this->assertTrue($version->published);
    }
}
