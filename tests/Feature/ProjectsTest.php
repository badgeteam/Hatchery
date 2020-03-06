<?php

namespace Tests\Feature;

use App\Mail\ProjectNotificationMail;
use App\Models\Badge;
use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Models\Warning;
use App\Support\GitRepository;
use App\Support\Helpers;
use Cz\Git\GitException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class ProjectsTest.
 *
 * @author annejan@badge.team
 */
class ProjectsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Unit test setup use Mail faker.
     */
    public function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Check the projects list.
     */
    public function testProjectsIndexPublic(): void
    {
        $response = $this
            ->get('/projects');
        $response->assertStatus(200)
            ->assertViewHas('projects', Project::paginate());
    }

    public function testProjectsIndex(): void
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
    public function testProjectsCreate(): void
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects/create');
        $response->assertStatus(200);
    }

    /**
     * Check the projects creation page exists.
     */
    public function testProjectsImport(): void
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/import');
        $response->assertStatus(200);
        $response->assertViewHas('type', 'import');
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStore(): void
    {
        $user = factory(User::class)->create();
        $this->assertEmpty(Project::all());
        $category = factory(Category::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $this->faker->name, 'description' => $this->faker->paragraph, 'category_id' => $category->id, 'status' => 'unknown']);
        $this->assertNotNull(Project::get()->last());
        $response->assertRedirect('/projects/'.Project::get()->last()->slug.'/edit')->assertSessionHas('successes');
        $this->assertCount(1, Project::all());
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreUnique(): void
    {
        $user = factory(User::class)->create();
        $this->assertEmpty(Project::all());
        $category = factory(Category::class)->create();
        $name = $this->faker->name;
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $name, 'description' => $this->faker->paragraph, 'category_id' => $category->id, 'status' => 'unknown']);
        $this->assertNotNull(Project::get()->last());
        $response->assertRedirect('/projects/'.Project::get()->last()->slug.'/edit')->assertSessionHas('successes');
        $this->assertCount(1, Project::all());
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $name, 'description' => $this->faker->paragraph, 'category_id' => $category->id]);
        $response->assertRedirect('')->assertSessionHasErrors();

        $name .= '_'; // issue found by fox name is unique, slug is identical (the + becomes plus now)
        $this->assertCount(1, Project::all());
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $name, 'description' => $this->faker->paragraph, 'category_id' => $category->id]);
        $response->assertRedirect('/projects/create')->assertSessionHasErrors();
        $this->assertCount(1, Project::all());
    }

    /**
     * Check the projects edit page functions.
     */
    public function testProjectsEdit(): void
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
     * Check the projects edit page functions for other users.
     */
    public function testProjectsEditOtherUser(): void
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
     * Check the projects edit page functions for other users.
     */
    public function testProjectsEditCollaboratingUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $project->collaborators()->attach($otherUser);
        $response = $this
            ->actingAs($otherUser)
            ->get('/projects/'.$project->slug.'/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsUpdate(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description'  => $this->faker->paragraph,
                'dependencies' => [$projectDep->id],
                'category_id'  => $project->category_id,
                'status'       => 'unknown',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // add deps
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertCount(1, $project->dependencies);
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description' => $this->faker->paragraph,
                'category_id' => $project->category_id,
                'status'      => 'unknown',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // remove deps
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEmpty($project->dependencies);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsUpdateBrokenBadge(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description' => $this->faker->paragraph,
                'category_id' => $project->category_id,
                'badge_ids'   => [1],  // non-existing badge ;)
                'status'      => 'unknown',
            ]);
        $response->assertRedirect('/projects/'.$project->slug.'/edit')->assertSessionHasErrors();
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsUpdateCollaborators(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description'   => $this->faker->paragraph,
                'category_id'   => $project->category_id,
                'collaborators' => [$otherUser->id],
                'status'        => 'unknown',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // add collaborator
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertCount(1, $project->collaborators);
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description' => $this->faker->paragraph,
                'category_id' => $project->category_id,
                'status'      => 'unknown',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // remove collaborator
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEmpty($project->collaborators);
    }

    /**
     * Check the projects can't be stored by other users.
     */
    public function testProjectsUpdateOtherUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description'  => $this->faker->paragraph,
                'dependencies' => [$projectDep->id],
                'category_id'  => $project->category_id,
                'status'       => 'unknown',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // add deps
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/projects/'.$project->slug, [
                'description' => $this->faker->paragraph,
                'category_id' => $project->category_id,
                'status'      => 'unknown',
            ]);
        $response->assertStatus(403);
        // remove deps
    }

    /**
     * Check the projects can be stored by collaborating users.
     */
    public function testProjectsUpdateCollaboratingUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $project->collaborators()->attach($otherUser);
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/projects/'.$project->slug, [
                'description'  => $this->faker->paragraph,
                'dependencies' => [$projectDep->id],
                'category_id'  => $project->category_id,
                'status'       => 'unknown',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // add deps
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/projects/'.$project->slug, [
                'description' => $this->faker->paragraph,
                'category_id' => $project->category_id,
                'status'      => 'unknown',
            ]);
        $response->assertStatus(403);
        // remove deps
    }

    /**
     * Check the projects can be published.
     */
    public function testProjectsPublish(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $project->dependencies()->save($projectDep);
        /** @var Collection $versions */
        $versions = $project->versions()->unPublished();
        /** @var Version $version */
        $version = $versions->first();
        $file = factory(File::class)->create(['version_id' => $version->id]);
        $file->first()->version_id = $version->id; // yah ugly
        $file->first()->save(); // wut?
        $this->assertNull($project->published_at);

        $response = $this
            ->actingAs($user)
            ->call('post', '/release/'.$project->slug);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        /** @var Collection $versions */
        $versions = Version::published()->where('project_id', $project->id)->get();
        /** @var Version $version */
        $version = $versions->last();

        $zip = (string) $version->zip;
        $this->assertFileExists(public_path($zip));
        $this->assertFileNotExists(public_path(str_replace('.gz', '', $zip)));

        $p = new \PharData(public_path($zip));
        $this->assertEquals(\Phar::GZ, $p->isCompressed());

        exec('tar xf '.public_path($zip).' -C '.sys_get_temp_dir());

        $path = sys_get_temp_dir().'/'.$project->slug;
        $json = (string) file_get_contents($path.'/metadata.json');

        $this->assertJsonStringEqualsJsonString((string) json_encode([
            'name'        => $project->name,
            'description' => null,
            'category'    => $project->category,
            'author'      => $project->user->name,
            'revision'    => 1,
        ]), $json);

        $dep = file_get_contents($path.'/'.$project->slug.'.egg-info/requires.txt');
        $this->assertEquals($projectDep->slug."\n", $dep);

        unlink(public_path($zip));
        Helpers::delTree($path);
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertNotNull($project->published_at);
        $this->assertTrue(now()->isSameDay($project->published_at));
    }

    /**
     * Check published project with icon has correct metadata.
     */
    public function testProjectsPublishIconMeta(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $file = factory(File::class)->create([
            'version_id' => $project->versions()->unPublished()->first()->id,
            'content'    => 'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAA7EAAAOxAGVKw4bAAAF'.
                'FElEQVRYw+1XfVDTZRxXj9oxN4a8bGMbG4ONTdBUEuVFQImjk5ISxBOQN3kJDSFAQgMRQh0ahLyj'.
                'EBAblaF2cnqWRml1h1meCUmGQhmVvQioMC8RsM8ToxR/v59o/FF3/u6e+217vs/3+/m+fb7Ppkx5'.
                '9Pyfnp2r+a5n8+WaAa3q+IBO3Y339QGtulevVbX31Ni/eyRDunat7wz+pBs+sEESAGOtep3qZl+d'.
                '/fvHNktfKYwUBHyYJX0qO8jCt2adVWRbgW0RgHQQmct7lLUbnze3/teGIxfzzK/U2DcN6FQDMLA1'.
                '2pt3l3cAkz3+TGGEwKu31v4jAO4/ud0m5qGNZwSYy6Hk4rU3VS2blpvLx++nLjNzRPhvwVMp1fmj'.
                'm6VRBPilckXhAxuP8zEVwHjXb9X2B7xnsVlUMhWxwjC9Tn37p93KKjo9JWuEzkhJT2epIm/CxmdL'.
                'WVMRwua+OtWJxQ7sx+nkymOF4QQADAwm+ZmJQz1MLJuzpKvGy1W9YOUOmT8+yJQGTAgACiwainuh'.
                'VEQnczxb5v9xtsxjFID6du06q/CmdAkJeRuV/OfbbdL1WvXl0EUmXEbj8PgxhL4bxZPMJNdRbKeF'.
                '3DtY1wiAUxobL3z+FkZGVrpxeePlXZTGRmT/bL5tBiMAeBIIwd7lC7hsJrkLxXYNxDBk96IQr5L3'.
                'WDTWeJvKqM40pVvHIULdnjONp9EqRkE1dFcoq++Xpk9eleUaDI5A6fCYcfDAcMBCLofqTJALl4f9'.
                'm5oQvgutYnhySbteFMxk/ODLkqWnd8jdwYQjfxs2rH6tqoXpLNlHjaVSbj49d7ox8SjN3+wJJiXn'.
                'd9nVAGgV1utYPQbPSRquvp0kdquME86HDjnV2fZC22qkbw+l4hUuXDFRttrTRMQEAGlqInI/lCuS'.
                '614UOZLPRzOlridyZPO6KxVliMwA0jCd6mzzFqnmlyrlfkrFgS5cEVEW4cUTMwHoLLHTjhageqi/'.
                'XlVkKMYv8P2vlGAo7Wdo3zwA2Ee56TObzSJFBNp1otoP8+QJ30oS++xLlcSO5RwFOHRnDcD73sxA'.
                'CwVD99QihRW03gHAhcYUSQTVXmOKmLTRxZJogQW8PATZXw3e3zC8WwvC+XOYogeeOH14k3UircB3'.
                'ZYo3wP8NVHuYhruJIUzHg+AJI7RiMPnekCjyAMNZz7JmTfWcyTbatspSTjNZ+QAwlLXCYi4tgNJo'.
                '4VIyRiFsSkc+ZP1YqdgKuXbk/ZxheAlBzQlkeuaH8xdQckeOLAUp6mAkmHk2rGmEMtEuW+6p4Cxp'.
                'xvjcQ/YbDJouvaEAMcQOU+l9xoljTCj+s1xZ4n2H0d5kMehYrdeE8pV3/h7jbSrSG7ifasHA7+h/'.
                'GQ137MB+J7iGNaGJiCtVIw58Fex+9/TaFSmYj9/P3wMADFoUKaDM7aGNEn+cGdStFy2e8J3gWScO'.
                '93q96gxuQ59GLfmnHnJWWjgg3IMEHIyew7sFBJT2nDOHQ0PbfqRLQN0vPfCtKHiRiSUAnCIXzap4'.
                'K2fy25d58nzk/IYmxNKB6SzphtYC20xyWTmzU77hoe+FSxzZxl2ligqAuIW01OGe4FYcJbChjdyT'.
                'HBa8DhntENXPIC2/SbmWvxbGXwjyOYI2Gobir3HHK8N8T6yOtwqrTxBFgWIzCQXD4z6k6Eprvm1u'.
                'kKsJb9L/H8T7zpC9lyZJ+L5coQOQk6QNsdqQqmMIdWFZjHCZ75wJVvqj57/y/AkQ6a2eMiXbygAA'.
                'AABJRU5ErkJggg==',
            'name' => 'icon.png',
        ]);
        $this->assertNull($project->published_at);

        $response = $this
            ->actingAs($user)
            ->call('post', '/release/'.$project->slug);
        $response->assertRedirect();
        /** @var Collection $versions */
        $versions = Version::published()->where('project_id', $project->id)->get();
        /** @var Version $version */
        $version = $versions->last();
        $zip = (string) $version->zip;
        exec('tar xf '.public_path($zip).' -C '.sys_get_temp_dir());
        $path = sys_get_temp_dir().'/'.$project->slug;
        $json = (string) file_get_contents($path.'/metadata.json');

        $this->assertJsonStringEqualsJsonString((string) json_encode([
            'name'        => $project->name,
            'description' => null,
            'category'    => $project->category,
            'author'      => $project->user->name,
            'revision'    => 1,
            'icon'        => 'icon.png',
        ]), $json);

        unlink(public_path($zip));
        Helpers::delTree($path);
    }

    /**
     * Check the projects can be deleted.
     */
    public function testProjectsDestroy(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('delete', '/projects/'.$project->slug);
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
    }

    /**
     * Check the projects can't be deleted by other users.
     */
    public function testProjectsDestroyOtherUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->call('delete', '/projects/'.$project->slug);
        $response->assertStatus(403);
    }

    /**
     * Check the projects can be deleted by admin users.
     */
    public function testProjectsDestroyAdminUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create(['admin' => true]);
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->call('delete', '/projects/'.$project->slug);
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
    }

    /**
     * Check the projects can be viewed (publicly).
     */
    public function testProjectsView(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->call('get', '/projects/'.$project->slug);
        $response->assertStatus(200)->assertViewHas(['project']);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreBadge(): void
    {
        $user = factory(User::class)->create();
        $this->assertEmpty(Project::all());
        $category = factory(Category::class)->create();
        $badge = factory(Badge::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', [
                'name'        => $this->faker->name,
                'description' => $this->faker->paragraph,
                'category_id' => $category->id,
                'badge_ids'   => [$badge->id],
                'status'      => 'unknown', ]);
        $this->assertNotNull(Project::get()->last());
        $response->assertRedirect('/projects/'.Project::get()->last()->slug.'/edit')->assertSessionHas('successes');
        $this->assertCount(1, Project::all());
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsUpdateBadge(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $badge = factory(Badge::class)->create();
        $this->assertEquals('unknown', $project->status);
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description'  => $this->faker->paragraph,
                'category_id'  => $project->category_id,
                'badge_ids'    => [$badge->id],
                'badge_status' => [$badge->id => 'working'],
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertCount(1, $project->states);
        $this->assertEquals('working', $project->status);
    }

    /**
     * Check that badge.team can be notified of dangerous projects.
     */
    public function testProjectsNotify(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/notify/'.$project->slug, ['description' => 'het zuigt']);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHas('successes');
        Mail::assertSent(ProjectNotificationMail::class, function (ProjectNotificationMail $mail) {
            Container::getInstance()->call([$mail, 'build']);

            return 'mails.projectNotify' === $mail->build()->textView;
        });
        /** @var Collection $warnings */
        $warnings = Warning::all();
        $this->assertCount(1, $warnings);
        /** @var Project $project */
        $project = Project::find($project->id);
        /** @var Warning $warning */
        $warning = $project->warnings()->first();
        $this->assertEquals('het zuigt', $warning->description);
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
        $this->assertEmpty(Project::all());
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

        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/'.$project->slug.'/pull');
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals($hash, $project->git_commit_id);
        $this->assertEquals(1, $project->revision);
        Helpers::delTree($folder);
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

    /**
     * Check the projects can't be renamed.
     */
    public function testProjectsRename(): void
    {
        $name = $this->faker->name;
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $this->assertNotEquals($name, $project->name);
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects/'.$project->slug.'/move', [
                'name' => $name,
            ]);
        $response->assertStatus(403);
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertNotEquals($name, $project->name);
        $this->assertNotEquals(Str::slug($name, '_'), $project->slug);
    }

    /**
     * Check admin can rename project.
     */
    public function testProjectsRenameAdminUser(): void
    {
        $name = $this->faker->name;
        $user = factory(User::class)->create([
            'admin' => true,
        ]);
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $this->assertNotEquals($name, $project->name);
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects/'.$project->slug.'/move', [
                'name' => $name,
            ]);
        $response->assertRedirect('/projects/'.Str::slug($name, '_').'/edit')->assertSessionHas('successes');
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals($name, $project->name);
        $this->assertEquals(Str::slug($name, '_'), $project->slug);
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects/'.$project->slug.'/move', [
                'name' => $name.'_',    // different name, same slug
            ]);
        $response->assertStatus(302)->assertSessionHasErrors();
    }

    /**
     * Check the projects can't be renamed.
     */
    public function testProjectsRenameForm(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/'.$project->slug.'/rename');
        $response->assertStatus(403);
    }

    /**
     * Check admin can rename project.
     */
    public function testProjectsRenameFormAdminUser(): void
    {
        $user = factory(User::class)->create([
            'admin' => true,
        ]);
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('get', '/projects/'.$project->slug.'/rename');
        $response->assertStatus(200);
    }

    /**
     * Check the projects list.
     */
    public function testProjectsIndexBadge(): void
    {
        $badge = factory(Badge::class)->create();
        $response = $this
            ->get('/projects?badge='.$badge->slug);
        $response->assertStatus(200)
            ->assertViewHas('projects')
            ->assertViewHas('badge', $badge->slug);
    }

    /**
     * Check the projects list.
     */
    public function testProjectsIndexCategory(): void
    {
        $category = factory(Category::class)->create();
        $response = $this
            ->get('/projects?category='.$category->slug);
        $response->assertStatus(200)
            ->assertViewHas('category', $category->slug)
            ->assertViewHas('projects');
    }

    /**
     * Check the projects list.
     */
    public function testProjectsIndexCategoryBadge(): void
    {
        $badge = factory(Badge::class)->create();
        $category = factory(Category::class)->create();
        $response = $this
            ->get('/projects?badge='.$badge->slug.'&category='.$category->slug);
        $response->assertStatus(200)
            ->assertViewHas('badge', $badge->slug)
            ->assertViewHas('category', $category->slug)
            ->assertViewHas('projects');
    }

    /**
     * Search the projects list.
     */
    public function testProjectsIndexSearch(): void
    {
        $response = $this
            ->post('/search', [
                'search' => 'meaning',
            ]);
        $response->assertStatus(200)
            ->assertViewHas('search', 'meaning')
            ->assertViewHas('projects');
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreForbiddenName(): void
    {
        $user = factory(User::class)->create();
        $category = factory(Category::class)->create();

        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => 'woezel', 'description' => $this->faker->paragraph, 'category_id' => $category->id]);
        $response->assertRedirect('/projects/create')->assertSessionHasErrors();

        $this->assertEmpty(Project::all());
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreNameTooLong(): void
    {
        $user = factory(User::class)->create();
        $category = factory(Category::class)->create();

        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $this->faker->text(1024), 'description' => $this->faker->paragraph, 'category_id' => $category->id]);
        $response->assertRedirect('/projects/create')->assertSessionHasErrors();

        $this->assertEmpty(Project::all());
    }

    /**
     * Check the projects can be published on update.
     */
    public function testProjectsUpdatePublish(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description' => $this->faker->paragraph,
                'category_id' => $project->category_id,
                'status'      => 'unknown',
                'publish'     => 1,
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        /** @var Project $project */
        $project = Project::find($project->id);
        /** @var Collection $versions */
        $versions = Version::published()->where('project_id', $project->id)->get();
        /** @var Version $version */
        $version = $versions->last();
        $zip = (string) $version->zip;
        unlink(public_path($zip));
    }
}
