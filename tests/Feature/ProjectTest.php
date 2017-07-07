<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\File;
use App\Models\User;
use App\Models\Project;
use App\Models\Version;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Factory;
use Session;

class ProjectTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Check the projects list.
     */
    public function testProjectsIndex()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects');
        $response->assertStatus(200)
            ->assertViewHas('projects', Project::paginate());
    }

    /**
     * Check the projects creation page exists.
     */
    public function testProjectsCreate()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects/create');
        $response->assertStatus(200);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStore()
    {
        $user = factory(User::class)->create();
        $faker = Factory::create();
        $this->assertEmpty(Project::all());
        $category = factory(Category::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $faker->name, 'description' => $faker->paragraph, 'category_id' => $category->id]);
        $response->assertRedirect('/projects/'.Project::get()->last()->slug.'/edit')->assertSessionHas('successes');
        $this->assertCount(1, Project::all());
    }

    /**
     * Check the projects edit page functions.
     */
    public function testProjectsEdit()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects/'.$project->slug.'/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the projects edit page functions for other users.
     */
    public function testProjectsEditOtherUser()
    {
        $user = factory(User::class)->create();
	$otherUser = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->get('/projects/'.$project->slug.'/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsUpdate()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        $project = factory(Project::class)->create();
        $faker = Factory::create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/' . $project->slug, ['description' => $faker->paragraph, 'dependencies' => [$projectDep->id]]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // add deps
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/' . $project->slug, ['description' => $faker->paragraph]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // remove deps
    }

    /**
     * Check the projects can't be stored by other users.
     */
    public function testProjectsUpdateOtherUser()
    {
        $user = factory(User::class)->create();
	$otherUser = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        $project = factory(Project::class)->create();
        $faker = Factory::create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/' . $project->slug, ['description' => $faker->paragraph, 'dependencies' => [$projectDep->id]]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // add deps
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/projects/' . $project->slug, ['description' => $faker->paragraph]);
        $response->assertStatus(403);
        // remove deps
    }

    /**
     * Check the projects can be published.
     */
    public function testProjectsPublish()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        $project = factory(Project::class)->create();
        $project->dependencies()->save($projectDep);
        $file = factory(File::class, ['version_id' => $project->versions()->unPublished()->first()->id])->create();
        $file->first()->version_id = $project->versions()->unPublished()->first()->id; // yah ugly
        $file->first()->save(); // wut?

        $response = $this
            ->actingAs($user)
            ->call('post', '/release/' . $project->slug);
        $response->assertRedirect('/projects/'.$project->slug.'/edit')->assertSessionHas('successes');

        $version = Version::published()->where('project_id', $project->id)->get()->last();

        $this->assertFileExists(public_path($version->zip));
        unlink(public_path($version->zip));
    }

    /**
     * Check the projects can be deleted.
     */
    public function testProjectsDestroy()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('delete', '/projects/' . $project->slug);
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
    }

    /**
     * Check the projects can't be deleted by other users.
     */
    public function testProjectsDestroyOtherUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->call('delete', '/projects/' . $project->slug);
        $response->assertStatus(403);
    }
}
