<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FileTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

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

    /**
     * Check the size of content helper.
     */
    public function testFileSizeOfContentAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['content' => 0]);
        $this->assertNull($file->size_of_content);
        $file = factory(File::class)->create(['content' => '123']);
        $this->assertEquals(3, $file->size_of_content);
    }

    /**
     * Assert py file mime type.
     */
    public function testFilePythonMimeAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.py']);
        $this->assertEquals('application/x-python-code', $file->mime);
    }

    /**
     * Assert png file mime type.
     */
    public function testFilePngMimeAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.png']);
        $this->assertEquals('image/png', $file->mime);
    }

    /**
     * Assert unknown (octet-stream) file mime type.
     */
    public function testFileUnknownMimeAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.unknown']);
        $this->assertEquals('application/octet-stream', $file->mime);
    }

    /**
     * Assert wav file mime type.
     */
    public function testFileWavMimeAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.wav']);
        $this->assertEquals('audio/wave', $file->mime);
    }

    /**
     * Assert bin (octet-stream) file mime type.
     */
    public function testBinMimeAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.bin']);
        $this->assertEquals('application/octet-stream', $file->mime);
    }
}
