<?php

namespace Tests\Feature;

use App\Events\ProjectUpdated;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Support\GitRepository;
use App\Support\Helpers;
use Cz\Git\GitException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class ProjectsTest.
 *
 * @author annejan@badge.team
 */
class ProjectsGitTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Check the projects edit page functions.
     */
    public function testProjectsEditGit(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create(['git' => $this->faker->url]);
        $response = $this
            ->actingAs($user)
            ->get('/projects/'.$project->slug.'/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreGit(): void
    {
        $name = $this->faker->name;
        $folder = sys_get_temp_dir().'/'.Str::slug($name, '_');
        mkdir($folder);

        $hash = $this->faker->sha256;
        $mock = $this->mock(GitRepository::class); // twice since folder is not real git repo
        $mock->expects('cloneRepository')->twice()->andReturnSelf();
        $mock->expects('getLastCommitId')->twice()->andReturns($hash);
        $this->app->instance(GitRepository::class, $mock);

        $user = factory(User::class)->create();
        $category = factory(Category::class)->create();
        $this->assertEmpty(Project::all());
        $response = $this
            ->actingAs($user)
            ->call('post', '/import-git', ['name' => $name, 'git' => $this->faker->url, 'category_id' => $category->id, 'status' => 'unknown']);
        $this->assertNotNull(Project::get()->last());
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
        $this->assertCount(1, Project::all());
        /** @var Project $project */
        $project = Project::get()->last();
        $this->assertEquals($hash, $project->git_commit_id);
        Helpers::delTree($folder);
    }

    public function testProjectsStoreGitTooLong(): void
    {
        $name = $this->faker->name;
        $folder = sys_get_temp_dir().'/'.Str::slug($name, '_');
        mkdir($folder);

        $user = factory(User::class)->create();
        $this->be($user);
        $category = factory(Category::class)->create();
        $this->assertEmpty(Project::all());

        $mock = $this->mock(GitRepository::class);
        $mock->expects('cloneRepository')->once()->andReturnSelf();
        $this->app->instance(GitRepository::class, $mock);

        $response = $this
            ->actingAs($user)
            ->call('post', '/import-git', ['name' => $name, 'git' => $this->faker->text(1024), 'category_id' => $category->id, 'status' => 'unknown']);
        $response->assertRedirect('/import')->assertSessionHasErrors();
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreGitFailures(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $category = factory(Category::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/import-git', ['name' => $project->name, 'git' => $this->faker->url, 'category_id' => $category->id, 'status' => 'unknown']);
        $this->assertCount(1, Project::all());
        $response->assertRedirect('')->assertSessionHasErrors();

        $response = $this
            ->actingAs($user)
            ->call('post', '/import-git', ['name' => $project->name.'_', 'git' => $this->faker->url, 'category_id' => $category->id, 'status' => 'unknown']);
        $this->assertCount(1, Project::all());  // Unique name, same slug
        $response->assertRedirect('/import')->assertSessionHasErrors();

        $response = $this
            ->actingAs($user)
            ->call('post', '/import-git', ['name' => 'badge', 'git' => $this->faker->url, 'category_id' => $category->id, 'status' => 'unknown']);
        $this->assertCount(1, Project::all());  // Illegal name (badge)
        $response->assertRedirect('/import')->assertSessionHasErrors();

        $mock = $this->mock(GitRepository::class); // twice since folder is not real git repo
        $mock->expects('cloneRepository')->once()->andThrowExceptions([new GitException()]);
        $this->app->instance(GitRepository::class, $mock);
        $response = $this
            ->actingAs($user)
            ->call('post', '/import-git', ['name' => $this->faker->name, 'git' => $this->faker->url, 'category_id' => $category->id, 'status' => 'unknown']);
        $this->assertCount(1, Project::all());
        $response->assertRedirect('/import')->assertSessionHasErrors();
    }

    /**
     * Check the projects can't be pulled.
     */
    public function testProjectsPullNoGit(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();

        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/'.$project->slug.'/pull');
        $response->assertRedirect('/projects/'.$project->slug.'/edit')->assertSessionHasErrors();
    }

    /**
     * Check the projects can be pulled.
     */
    public function testProjectsPullNothingToUpdate(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create([
            'git_commit_id' => $this->faker->sha256,
            'git'           => $this->faker->url,
        ]);
        /** @var Version $version */
        $version = $project->versions->first();
        $version->zip = 'test';
        $version->save();

        $folder = sys_get_temp_dir().'/'.$project->slug;
        mkdir($folder);

        $hash = $project->git_commit_id;
        $mock = $this->mock(GitRepository::class);
        $mock->expects('cloneRepository')->andReturnSelf();
        $mock->expects('getLastCommitId')->andReturns($hash);
        $this->app->instance(GitRepository::class, $mock);

        Event::fake();

        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/'.$project->slug.'/pull');
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals($hash, $project->git_commit_id);
        $this->assertEquals(1, $project->revision);
        Helpers::delTree($folder);

        Event::assertDispatched(ProjectUpdated::class, function ($e) {
            $this->assertEquals('info', $e->type);

            return true;
        });
    }

    /**
     * Check the projects can be pulled.
     */
    public function testProjectsPullClean(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create([
            'git_commit_id' => $this->faker->sha256,
            'git'           => $this->faker->url,
        ]);
        /** @var Version $version */
        $version = $project->versions->first();
        $version->zip = 'test';
        $version->save();

        $folder = sys_get_temp_dir().'/'.$project->slug;
        mkdir($folder);

        $hash = $this->faker->sha256;
        $mock = $this->mock(GitRepository::class);
        $mock->expects('cloneRepository')->andReturnSelf();
        $mock->expects('getLastCommitId')->twice()->andReturns($hash);
        $this->app->instance(GitRepository::class, $mock);

        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/'.$project->slug.'/pull');
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals($hash, $project->git_commit_id);
        $this->assertEquals(2, $project->revision);
        Helpers::delTree($folder);
    }

    /**
     * Check the projects can be pulled cheaply.
     */
    public function testProjectsPullRecycleFolder(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create([
            'git_commit_id' => $this->faker->sha256,
            'git'           => $this->faker->url,
        ]);
        /** @var Version $version */
        $version = $project->versions->first();
        $version->zip = 'test';
        $version->save();

        $folder = sys_get_temp_dir().'/'.$project->slug;
        mkdir($folder);
        mkdir($folder.'/.git');
        touch($folder.'/.git/HEAD');

        $hash = $this->faker->sha256;
        $mock = $this->mock(GitRepository::class);
        $mock->expects('open')->andReturnSelf();
        $mock->expects('pull')->andReturn();
        $mock->expects('getLastCommitId')->twice()->andReturns($hash);
        $this->app->instance(GitRepository::class, $mock);

        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/'.$project->slug.'/pull');
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals($hash, $project->git_commit_id);
        $this->assertEquals(2, $project->revision);
        Helpers::delTree($folder);
    }
}
