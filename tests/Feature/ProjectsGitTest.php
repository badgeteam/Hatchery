<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\ProjectUpdated;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Support\Helpers;
use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\GitRepository;
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
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create(['git' => $this->faker->url]);
        $response = $this
            ->actingAs($user)
            ->get('/projects/' . $project->slug . '/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreGit(): void
    {
        $name = $this->faker->name;
        $folder = sys_get_temp_dir() . '/' . Str::slug($name, '_');
        mkdir($folder);

        $hash = $this->faker->sha256;
        $mockRepo = $this->mock(GitRepository::class);
        $mockRepo->expects('getLastCommitId')->twice()->andReturns($hash);
        $this->app->instance(GitRepository::class, $mockRepo);
        $mockGit = $this->mock(Git::class);
        $mockGit->expects('cloneRepository')->twice()->andReturnSelf();
        $this->app->instance(Git::class, $mockGit);
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create();
        $this->assertEmpty(Project::all());
        $response = $this
            ->actingAs($user)
            ->call(
                'post',
                '/import-git',
                [
                    'name' => $name, 'git' => $this->faker->url, 'category_id' => $category->id, 'status' => 'unknown'
                ]
            );
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
        $folder = sys_get_temp_dir() . '/' . Str::slug($name, '_');
        mkdir($folder);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Category $category */
        $category = Category::factory()->create();
        $this->assertEmpty(Project::all());

        $mock = $this->mock(Git::class);
        $mock->expects('cloneRepository')->once()->andReturnSelf();
        $this->app->instance(Git::class, $mock);

        $response = $this
            ->actingAs($user)
            ->call(
                'post',
                '/import-git',
                [
                    'name' => $name, 'git' => $this->faker->text(1024),
                    'category_id' => $category->id, 'status' => 'unknown'
                ]
            );
        $response->assertRedirect('/import')->assertSessionHasErrors();
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreGitFailures(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create();
        $response = $this
            ->actingAs($user)
            ->call(
                'post',
                '/import-git',
                [
                    'name' => $project->name, 'git' => $this->faker->url,
                    'category_id' => $category->id, 'status' => 'unknown'
                ]
            );
        $this->assertCount(1, Project::all());
        $response->assertRedirect('')->assertSessionHasErrors();

        $response = $this
            ->actingAs($user)
            ->call(
                'post',
                '/import-git',
                [
                    'name' => $project->name . '_', 'git' => $this->faker->url,
                    'category_id' => $category->id, 'status' => 'unknown'
                ]
            );
        $this->assertCount(1, Project::all());  // Unique name, same slug
        $response->assertRedirect('/import')->assertSessionHasErrors();

        $response = $this
            ->actingAs($user)
            ->call(
                'post',
                '/import-git',
                [
                    'name' => 'badge', 'git' => $this->faker->url, 'category_id' => $category->id, 'status' => 'unknown'
                ]
            );
        $this->assertCount(1, Project::all());  // Illegal name (badge)
        $response->assertRedirect('/import')->assertSessionHasErrors();

        $mock = $this->mock(Git::class); // twice since folder is not real git repo
        $mock->expects('cloneRepository')->once()->andThrowExceptions([new GitException('Stuk')]);
        $this->app->instance(Git::class, $mock);
        $response = $this
            ->actingAs($user)
            ->call(
                'post',
                '/import-git',
                [
                    'name' => $this->faker->name, 'git' => $this->faker->url,
                    'category_id' => $category->id, 'status' => 'unknown'
                ]
            );
        $this->assertCount(1, Project::all());
        $response->assertRedirect('/import')->assertSessionHasErrors();
    }

    /**
     * Check the projects can't be pulled.
     */
    public function testProjectsPullNoGit(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();

        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/' . $project->slug . '/pull');
        $response->assertRedirect('/projects/' . $project->slug . '/edit')->assertSessionHasErrors();
    }

    /**
     * Check the projects can be pulled.
     */
    public function testProjectsPullNothingToUpdate(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create([
            'git_commit_id' => $this->faker->sha256,
            'git'           => $this->faker->url,
        ]);
        /** @var Version $version */
        $version = $project->versions->first();
        $version->zip = 'test';
        $version->save();

        $folder = sys_get_temp_dir() . '/' . $project->slug;
        mkdir($folder);

        $hash = $project->git_commit_id;
        $mock = $this->mock(Git::class);
        $mock->expects('cloneRepository')->andReturnSelf();
        $mock->expects('getLastCommitId')->andReturns($hash);
        $this->app->instance(Git::class, $mock);

        Event::fake();

        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/' . $project->slug . '/pull');
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
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create([
            'git_commit_id' => $this->faker->sha256,
            'git'           => $this->faker->url,
        ]);
        /** @var Version $version */
        $version = $project->versions->first();
        $version->zip = 'test';
        $version->save();

        $folder = sys_get_temp_dir() . '/' . $project->slug;
        mkdir($folder);

        $hash = $this->faker->sha256;
        $mock = $this->mock(Git::class);
        $mock->expects('cloneRepository')->andReturnSelf();
        $mock->expects('getLastCommitId')->twice()->andReturns($hash);
        $this->app->instance(Git::class, $mock);

        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/' . $project->slug . '/pull');
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
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create([
            'git_commit_id' => $this->faker->sha256,
            'git'           => $this->faker->url,
        ]);
        /** @var Version $version */
        $version = $project->versions->first();
        $version->zip = 'test';
        $version->save();

        $folder = sys_get_temp_dir() . '/' . $project->slug;
        mkdir($folder);
        mkdir($folder . '/.git');
        touch($folder . '/.git/HEAD');

        $hash = $this->faker->sha256;
        $mockGit = $this->mock(Git::class);
        $mockGit->expects('open')->andReturnSelf();
        $this->app->instance(Git::class, $mockGit);
        $mockRepo = $this->mock(GitRepository::class);
        $mockRepo->expects('pull')->andReturn();
        $mockRepo->expects('getLastCommitId')->twice()->andReturns($hash);
        $this->app->instance(GitRepository::class, $mockRepo);

        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/' . $project->slug . '/pull');
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals($hash, $project->git_commit_id);
        $this->assertEquals(2, $project->revision);
        Helpers::delTree($folder);
    }
}
