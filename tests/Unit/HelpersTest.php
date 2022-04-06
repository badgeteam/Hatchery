<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\User;
use App\Models\Version;
use App\Support\Helpers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class HelpersTest.
 *
 * @author annejan@badge.team
 */
class HelpersTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Assert the return value is false on a non existing folder.
     */
    public function testDelTreeOnNonExistingFolder(): void
    {
        $this->assertFalse(Helpers::delTree(sys_get_temp_dir().'/'.$this->faker->firstName));
    }

    /**
     * Assert folder is deleted.
     */
    public function testDelTreeOnEmptyFolder(): void
    {
        $folder = sys_get_temp_dir().'/'.$this->faker->firstName;
        mkdir($folder);
        $this->assertFileExists($folder);
        $this->assertTrue(Helpers::delTree($folder));
        $this->assertFileDoesNotExist($folder);
    }

    /**
     * Assert nested folder is deleted.
     */
    public function testDelTreeOnNestedFolder(): void
    {
        $folder = sys_get_temp_dir().'/'.$this->faker->firstName;
        mkdir($folder);
        $secondFolder = $folder.'/'.$this->faker->firstName;
        mkdir($secondFolder);
        $this->assertFileExists($folder);
        $this->assertFileExists($secondFolder);
        $this->assertTrue(Helpers::delTree($folder));
        $this->assertFileDoesNotExist($folder);
        $this->assertFileDoesNotExist($secondFolder);
    }

    /**
     * Assert nested file is deleted.
     */
    public function testDelTreeOnNestedFile(): void
    {
        $folder = sys_get_temp_dir().'/'.$this->faker->firstName;
        mkdir($folder);
        $file = $folder.'/'.$this->faker->firstName;
        touch($file);
        $this->assertFileExists($folder);
        $this->assertFileExists($file);
        $this->assertTrue(Helpers::delTree($folder));
        $this->assertFileDoesNotExist($folder);
        $this->assertFileDoesNotExist($file);
    }

    /**
     * Assert empty folder doesn't break helper.
     */
    public function testAddFilesEmptyFolder(): void
    {
        $folder = sys_get_temp_dir().'/'.$this->faker->firstName;
        mkdir($folder);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        Helpers::addFiles($folder, $version);
        /** @var Version $version */
        $version = Version::find($version->id);
        $this->assertEmpty($version->files);
        Helpers::delTree($folder);
    }

    /**
     * Assert random file is not added to Version.
     */
    public function testAddFilesIgnoresFile(): void
    {
        $folder = sys_get_temp_dir().'/'.$this->faker->firstName;
        mkdir($folder);
        $file = $folder.'/'.$this->faker->firstName;
        touch($file);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        Helpers::addFiles($folder, $version);
        /** @var Version $version */
        $version = Version::find($version->id);
        $this->assertEmpty($version->files);
        Helpers::delTree($folder);
    }

    /**
     * Assert file is not added to Version.
     */
    public function testAddFilesSingleFile(): void
    {
        $folder = sys_get_temp_dir().'/'.$this->faker->firstName;
        mkdir($folder);
        $file = $folder.'/'.$this->faker->firstName.'.py';
        touch($file);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        Helpers::addFiles($folder, $version);
        /** @var Version $version */
        $version = Version::find($version->id);
        $this->assertCount(1, $version->files);
        /** @var File $versionFile */
        $versionFile = $version->files->first();
        $this->assertEquals(str_replace($folder.'/', '', $file), $versionFile->name);
        Helpers::delTree($folder);
    }

    /**
     * Assert file is not added to Version.
     */
    public function testAddFilesNestedFile(): void
    {
        $folder = sys_get_temp_dir().'/'.$this->faker->firstName;
        mkdir($folder);
        $secondFolder = $folder.'/'.$this->faker->firstName;
        mkdir($secondFolder);
        $file = $secondFolder.'/'.$this->faker->firstName.'.py';
        touch($file);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        Helpers::addFiles($folder, $version);
        /** @var Version $version */
        $version = Version::find($version->id);
        $this->assertCount(1, $version->files);
        /** @var File $versionFile */
        $versionFile = $version->files->first();
        $this->assertEquals(str_replace($folder.'/', '', $file), $versionFile->name);
        Helpers::delTree($folder);
    }
}
