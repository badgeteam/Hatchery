<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class VersionTest.
 *
 * @author annejan@badge.team
 */
class VersionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Assert the Version has a relation with a single Project.
     */
    public function testVersionProjectRelationship(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $this->assertInstanceOf(Project::class, $version->project);
    }

    /**
     * Assert the Version can have a collection of Files.
     */
    public function testVersionFileRelationship(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $this->assertInstanceOf(Collection::class, $version->files);
        $this->assertEmpty($version->files);
    }

    /**
     * Check if published (anything with a zip) functions as expected.
     */
    public function testVersionPublishedHelper(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $this->assertFalse($version->published);
        $version->zip = 'iets';
        $this->assertTrue($version->published);
    }

    /**
     * Check if Version published and unPublished scopes function.
     */
    public function testVersionScopes(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        /** @var Collection<int, Version> $versions */
        $versions = Version::unPublished()->get();
        $this->assertCount(2, $versions);
        $versions = Version::published()->get();
        $this->assertEmpty($versions);
        $version->zip = 'iets anders';
        $version->save();
        /** @var Collection<int, Version> $versions */
        $versions = Version::unPublished()->get();
        $this->assertCount(1, $versions);
        /** @var Collection<int, Version> $versions */
        $versions = Version::published()->get();
        $this->assertCount(1, $versions);
    }
}
