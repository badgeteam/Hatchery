<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\ProjectUpdated;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Support\Helpers;
use CzProject\GitPhp\CommitId;
use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\GitRepository;
use CzProject\GitPhp\InvalidArgumentException;
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
     * @throws InvalidArgumentException
     */
    public function testProjectsStoreGit(): void
    {
        $name = $this->faker->name;
        $folder = sys_get_temp_dir() . '/' . Str::slug($name, '_');
        mkdir($folder);

        $hash = $this->faker->sha1;
        $mockRepo = $this->mock(GitRepository::class);
        $mockRepo->expects('getLastCommitId')->twice()->andReturns(new CommitId($hash));
        $this->app->instance(GitRepository::class, $mockRepo);
        $mockGit = $this->mock(Git::class);
        $mockGit->expects('cloneRepository')->twice()->andReturns($mockRepo);
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

    /**
     * A git backed project must not get the seeded empty __init__.py: the
     * clone brings its own, and FilePolicy forbids deleting files from a git
     * project, so the duplicate could never be removed.
     */
    public function testProjectsImportGitHasNoSeededInitFile(): void
    {
        $name = $this->faker->name;
        $folder = sys_get_temp_dir() . '/' . Str::slug($name, '_');
        mkdir($folder);

        $hash = $this->faker->sha1;
        $mockRepo = $this->mock(GitRepository::class);
        $mockRepo->expects('getLastCommitId')->twice()->andReturns(new CommitId($hash));
        $this->app->instance(GitRepository::class, $mockRepo);
        $mockGit = $this->mock(Git::class);
        $mockGit->expects('cloneRepository')->twice()->andReturns($mockRepo);
        $this->app->instance(Git::class, $mockGit);
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create();

        $this->actingAs($user)->call(
            'post',
            '/import-git',
            ['name' => $name, 'git' => $this->faker->url, 'category_id' => $category->id, 'status' => 'unknown']
        );

        /** @var Project $project */
        $project = Project::get()->last();
        $this->assertNotNull($project->git);
        /** @var Version $version */
        $version = $project->versions->last();
        $this->assertCount(0, $version->files()->where('name', '__init__.py')->get());

        Helpers::delTree($folder);
    }

    /**
     * A project without a git repository still gets its empty __init__.py.
     */
    public function testProjectsStoreWithoutGitKeepsInitFile(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create();

        $this->actingAs($user)->call(
            'post',
            '/projects',
            ['name' => $this->faker->name, 'category_id' => $category->id, 'status' => 'unknown']
        );

        /** @var Project $project */
        $project = Project::get()->last();
        $this->assertNull($project->git);
        /** @var Version $version */
        $version = $project->versions->last();
        $this->assertCount(1, $version->files()->where('name', '__init__.py')->get());
    }

    /**
     * The import clone directory is keyed on the project name, so a leftover
     * from an earlier import of that name made git refuse to clone and the
     * user saw a temp directory error (#102).
     */
    public function testProjectsImportGitClearsStaleTempFolder(): void
    {
        $name = $this->faker->name;
        $importFolder = sys_get_temp_dir() . '/' . Str::slug($name);
        $updateFolder = sys_get_temp_dir() . '/' . Str::slug($name, '_');
        mkdir($updateFolder);

        // Left behind by an earlier import of the same name.
        mkdir($importFolder);
        file_put_contents($importFolder . '/leftover.txt', 'stale');

        $hash = $this->faker->sha1;
        $mockRepo = $this->mock(GitRepository::class);
        $mockRepo->expects('getLastCommitId')->twice()->andReturns(new CommitId($hash));
        $this->app->instance(GitRepository::class, $mockRepo);
        $mockGit = $this->mock(Git::class);
        $mockGit->expects('cloneRepository')->twice()->andReturns($mockRepo);
        $this->app->instance(Git::class, $mockGit);
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->call(
            'post',
            '/import-git',
            ['name' => $name, 'git' => $this->faker->url, 'category_id' => $category->id, 'status' => 'unknown']
        );

        $response->assertRedirect('/projects/')->assertSessionHas('successes');
        // Cleared before cloning, and not left behind for the next import.
        $this->assertDirectoryDoesNotExist($importFolder);

        Helpers::delTree($updateFolder);
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
     * @throws InvalidArgumentException
     */
    public function testProjectsPullNothingToUpdate(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create([
            'git_commit_id' => $this->faker->sha1,
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
        $mock->expects('getLastCommitId')->andReturns(new CommitId((string)$hash));
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
            'git_commit_id' => $this->faker->sha1,
            'git'           => $this->faker->url,
        ]);
        /** @var Version $version */
        $version = $project->versions->first();
        $version->zip = 'test';
        $version->save();

        $folder = sys_get_temp_dir() . '/' . $project->slug;
        mkdir($folder);

        $hash = $this->faker->sha1;
        $mock = $this->mock(Git::class);
        $mock->expects('cloneRepository')->andReturnSelf();
        $mock->expects('getLastCommitId')->twice()->andReturns(new CommitId($hash));
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
            'git_commit_id' => $this->faker->sha1,
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

        $hash = $this->faker->sha1;
        $mockRepo = $this->mock(GitRepository::class);
        $mockRepo->expects('getLastCommitId')->twice()->andReturns(new CommitId((string)$hash));
        $mockRepo->expects('pull')->andReturn();
        $this->app->instance(GitRepository::class, $mockRepo);
        $mockGit = $this->mock(Git::class);
        $mockGit->expects('open')->andReturns($mockRepo);
        $this->app->instance(Git::class, $mockGit);

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
