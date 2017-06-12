<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\Version;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Project;

class FileTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Assert the File has a relation with a single Project Version.
     */
    public function testVersionProjectRelationship()
    {
        $file = factory(File::class)->create();
        $this->assertInstanceOf(Version::class, $file->version);
        $this->assertInstanceOf(Project::class, $file->version->project);
    }
}
