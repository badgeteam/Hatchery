<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\Version;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Project;
use App\Models\User;

class FileTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Assert the File has a relation with a single Project Version.
     */
    public function testFileVersionProjectRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $this->assertInstanceOf(Version::class, $file->version);
        $this->assertInstanceOf(Project::class, $file->version->project);
    }

    /**
     * Assert File extension helper work in a basic case.
     */
    public function testFileExtensionAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.txt']);
        $this->assertEquals('txt', $file->extension);
    }

    /**
     * Assert txt Files are flagged editable.
     */
    public function testFileEditableAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.txt']);
        $this->assertTrue($file->editable);
    }
}
