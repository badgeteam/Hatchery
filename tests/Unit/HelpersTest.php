<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\User;
use App\Models\Version;
use App\Support\Helpers;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class HelpersTest.
 *
 * @author annejan@badge.team
 */
class HelpersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Assert the Warning is cast on a non existing folder.
     */
    public function testDelTreeOnNonExistingFolder(): void
    {
        $faker = Factory::create();
        $this->expectException(\ErrorException::class);
        $this->assertFalse(Helpers::delTree(sys_get_temp_dir() . '/' . $faker->firstName));
    }

    /**
     * Assert folder is deleted.
     */
    public function testDelTreeOnEmptyFolder(): void
    {
        $faker = Factory::create();
        $folder = sys_get_temp_dir() . '/' . $faker->firstName;
        mkdir($folder);
        $this->assertFileExists($folder);
        $this->assertTrue(Helpers::delTree($folder));
        $this->assertFileNotExists($folder);
    }

    /**
     * Assert nested folder is deleted.
     */
    public function testDelTreeOnNestedFolder(): void
    {
        $faker = Factory::create();
        $folder = sys_get_temp_dir() . '/' . $faker->firstName;
        mkdir($folder);
        $secondFolder = $folder . '/' . $faker->firstName;
        mkdir($secondFolder);
        $this->assertFileExists($folder);
        $this->assertFileExists($secondFolder);
        $this->assertTrue(Helpers::delTree($folder));
        $this->assertFileNotExists($folder);
        $this->assertFileNotExists($secondFolder);
    }

    /**
     * Assert nested file is deleted.
     */
    public function testDelTreeOnNestedFile(): void
    {
        $faker = Factory::create();
        $folder = sys_get_temp_dir() . '/' . $faker->firstName;
        mkdir($folder);
        $file = $folder . '/' . $faker->firstName;
        touch($file);
        $this->assertFileExists($folder);
        $this->assertFileExists($file);
        $this->assertTrue(Helpers::delTree($folder));
        $this->assertFileNotExists($folder);
        $this->assertFileNotExists($file);
    }

    /**
     * Assert empty folder doesn't break helper.
     */
    public function testAddFilesEmptyFolder(): void
    {
        $faker = Factory::create();
        $folder = sys_get_temp_dir() . '/' . $faker->firstName;
        mkdir($folder);
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
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
        $faker = Factory::create();
        $folder = sys_get_temp_dir() . '/' . $faker->firstName;
        mkdir($folder);
        $file = $folder . '/' . $faker->firstName;
        touch($file);
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
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
        $faker = Factory::create();
        $folder = sys_get_temp_dir() . '/' . $faker->firstName;
        mkdir($folder);
        $file = $folder . '/' . $faker->firstName . '.py';
        touch($file);
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
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
        $faker = Factory::create();
        $folder = sys_get_temp_dir() . '/' . $faker->firstName;
        mkdir($folder);
        $secondFolder = $folder . '/' . $faker->firstName;
        mkdir($secondFolder);
        $file = $secondFolder . '/' . $faker->firstName . '.py';
        touch($file);
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
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