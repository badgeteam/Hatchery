<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VersionTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    /**
     * Assert the Version has a relation with a single Project.
     */
    public function testVersionProjectRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $this->assertInstanceOf(Project::class, $version->project);
    }

    /**
     * Assert the Version can have a collection of Files.
     */
    public function testVersionFileRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $this->assertInstanceOf(Collection::class, $version->files);
        $this->assertEmpty($version->files);
    }

    /**
     * Check if published (anything with a zip) functions as expected.
     */
    public function testVersionPublishedHelper()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $this->assertFalse($version->published);
        $version->zip = 'iets';
        $this->assertTrue($version->published);
    }

    /**
     * Check if Version published and unPublished scopes function.
     */
    public function testVersionScopes()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $this->assertCount(2, Version::unPublished()->get());
        $this->assertEmpty(Version::published()->get());
        $version->zip = 'iets anders';
        $version->save();
        $this->assertCount(1, Version::unPublished()->get());
        $this->assertCount(1, Version::published()->get());
    }
}
