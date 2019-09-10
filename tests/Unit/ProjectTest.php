<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Assert the Project has a relation with a single User.
     */
    public function testProjectUserRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertInstanceOf(User::class, $project->user);
    }

    /**
     * Assert the Project can have a collection of Versions.
     */
    public function testProjectVersionRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertInstanceOf(Collection::class, $project->versions);
    }

    /**
     * Assert the Project always has at-least one Version.
     */
    public function testNewProjectHasAVersion()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertCount(1, $project->versions);
    }

    /**
     * Check if Project revision helper.
     */
    public function testProjectRevisionAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $this->assertNull($version->project->revision);
        $version->zip = 'iets';
        $version->save();
        $this->assertEquals('1', $version->project->revision);
    }

    /**
     * Check if Project size_of_content helper works without release.
     */
    public function testProjectSizeOfContentNoRelease()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $this->assertEquals(0, $version->project->size_of_content);
    }

    /**
     * Assert the Project isForbidden.
     */
    public function testProjectForbiddenNames()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $this->assertTrue(Project::isForbidden('badge'));
        $this->assertTrue(Project::isForbidden('request'));
    }

    /**
     * Assert the Project can't use illegal name.
     */
    public function testProjectSaveForbiddenNames()
    {
        $this->expectException(\Exception::class);
        $user = factory(User::class)->create();
        $this->be($user);
        $project = new Project();
        $project->description = 'test bla';
        $project->name = 'ESP32';
        $project->save();
    }

    /**
     * Test the Category helper.
     */
    public function testProjectCategoryAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = new Project();
        $project->name = 'test';
        $project->save();

        $this->assertEquals('uncategorised', $project->category);

        $category = factory(Category::class)->create();
        $project->category()->associate($category);

        $this->assertEquals($category->slug, $project->category);
    }

    /**
     * Test the description helper.
     */
    public function testProjectDescriptionAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertNull($project->description);

        $file = factory(File::class)->create(['content' => 'Description', 'name' => 'ReadMe.md']);
        $this->assertEquals('Description', $file->version->project->description);
    }

    /**
     * Test the descriptionHtml (Markdown) helper.
     */
    public function testProjectDescriptionHtmlAttribute()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertNull($project->descriptionHtml);

        $file = factory(File::class)->create(['content' => "Description\n----------", 'name' => 'ReadMe.md']);
        $this->assertEquals('<h2>Description</h2>', $file->version->project->descriptionHtml);
    }

    /**
     * Test the userVoted helper.
     */
    public function testProjectUserVoted()
    {
        $project = new Project;
        $this->assertNull($project->userVoted());
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertFalse($project->userVoted());
        factory(Vote::class)->create(['project_id' => $project->id]);
        $this->assertTrue($project->userVoted());
    }
}
