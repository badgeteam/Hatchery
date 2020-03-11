<?php

namespace Tests\Unit;

use App\Events\ProjectUpdated;
use App\Models\Badge;
use App\Models\BadgeProject;
use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class ProjectTest.
 *
 * @author annejan@badge.team
 */
class ProjectTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Assert the Project has a relation with a single User.
     */
    public function testProjectUserRelationship(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertInstanceOf(User::class, $project->user);
    }

    /**
     * Assert the Project can have a collection of Versions.
     */
    public function testProjectVersionRelationship(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertInstanceOf(Collection::class, $project->versions);
    }

    /**
     * Assert the Project always has at-least one Version.
     */
    public function testNewProjectHasAVersion(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertCount(1, $project->versions);
    }

    /**
     * Check if Project revision helper.
     */
    public function testProjectRevisionAttribute(): void
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
    public function testProjectSizeOfContentNoRelease(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $this->assertEquals(0, $version->project->size_of_content);
    }

    /**
     * Assert the Project isForbidden.
     */
    public function testProjectForbiddenNames(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $this->assertTrue(Project::isForbidden('badge'));
        $this->assertTrue(Project::isForbidden('request'));
    }

    /**
     * Assert the Project can't use illegal name.
     */
    public function testProjectSaveForbiddenNames(): void
    {
        $this->expectException(\Exception::class);
        $user = factory(User::class)->create();
        $this->be($user);
        $project = new Project();
        $project->name = 'ESP32';
        $project->save();
    }

    /**
     * Test the Category helper.
     */
    public function testProjectCategoryAttribute(): void
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
    public function testProjectDescriptionAttribute(): void
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
    public function testProjectDescriptionHtmlAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertNull($project->descriptionHtml);

        $file = factory(File::class)->create(['content' => "Description\n----------", 'name' => 'ReadMe.md']);
        $this->assertEquals("<h2>Description</h2>\n", $file->version->project->descriptionHtml);
    }

    /**
     * Test the userVoted helper.
     */
    public function testProjectUserVoted(): void
    {
        $project = new Project();
        $this->assertNull($project->userVoted());
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertFalse($project->userVoted());
        factory(Vote::class)->create(['project_id' => $project->id]);
        $this->assertTrue($project->userVoted());
    }

    /**
     * Test the status helper (gets data from BadgeProject(s)).
     */
    public function testProjectStatusMagic(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertEquals('unknown', $project->status);
        $badge = factory(Badge::class)->create();
        $project->badges()->attach($badge);
        /** @var BadgeProject $state */
        $state = BadgeProject::where('badge_id', $badge->id)->where('project_id', $project->id)->first();
        $state->status = 'broken';
        $state->save();
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals('broken', $project->status);
        $badge = factory(Badge::class)->create();
        $project->badges()->attach($badge);
        /** @var BadgeProject $state */
        $state = BadgeProject::where('badge_id', $badge->id)->where('project_id', $project->id)->first();
        $state->status = 'in_progress';
        $state->save();
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals('in_progress', $project->status);
        $badge = factory(Badge::class)->create();
        $project->badges()->attach($badge);
        /** @var BadgeProject $state */
        $state = BadgeProject::where('badge_id', $badge->id)->where('project_id', $project->id)->first();
        $state->status = 'working';
        $state->save();
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals('working', $project->status);
        $badge = factory(Badge::class)->create();
        $project->badges()->attach($badge);
        /** @var BadgeProject $state */
        $state = BadgeProject::where('badge_id', $badge->id)->where('project_id', $project->id)->first();
        $state->status = 'in_progress';
        $state->save();
        /** @var Project $project */
        $project = Project::find($project->id); // stay at working
        $this->assertEquals('working', $project->status);
        $this->assertCount(4, BadgeProject::all());
    }

    /**
     * Test the hasValidIcon() helper.
     */
    public function testProjectHasValidIcon(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertFalse($project->hasValidIcon());
        $file = factory(File::class)->create([ // 32x32 pixel PNG
            'content' => base64_decode('iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAGWElEQVR42sVXCUxUVxQ9fxZm'.
                'YGRRWQQRHaoWY21dSqkpYo1tXEi0bdwwqVsXErVp01qwWlutxAWNrSXiXlcqYAiVtgpKi0tdAkVQoIhLGcAFF1YHhvnz//ze9/8A'.
                '4oYaqD9z8/68effdc+899743HJ7zwz2LUt4W/TSNGvMFEZor1+27Jy3l99H0XRLhfwFQsF2f7d1/TDB7v3g2vWLEp9aZ9JpPUtPp'.
                'APK36t936+6/u3foInAqLUzHlyE+0ZS4NlmIoZ9LnjYKTwXgVJzO1b0LVxIYtthX7/ceIImwVv6K8xkxlhkrmuZdqJB+o2V3SKRO'.
                'AXB+mz62e0Dwl34hSwCnnjIAWMtQmR+H9ENHc2fH8vPYMhJLhwM4t03fT6fTFfV9K1ar9ghVjEuCLKI5F5czv5ai4mtXpJ0St9Ly'.
                'ChJ7hwIg79N7vjJtTLegDwG16z0AaBSrUXM5BWcyE26MX2idRctPQ6mKjgFAZTfB3dP/QJ+wGHAuQaipsyLvHzNsNhsGGDUI8FVB'.
                'spbCdGIl4pNM+9YmCctJ7RKegJDtAijeqXcS7bgQGBZtdO4xnjTUyD5fj+paqwxABRHhYS4UiSZYqvJw/vDqxknfNkVevS2lk3oV'.
                '2iFkuwAo9IuJeDF+wZ8DWn+aEXEsuxb1Zh48z8sgpo1zVdIh3EFlYTJSUo+enP8j/xktLiCxPjMACr2/i8FQEjhqmYvGjfqOZJej'.
                'ejqvHtduNsrG3QySIwIEwM5D5G/jUuZy+9x1NYuz8u17SeHa46LwWADkfZL/4IgpXftSzas9FSMMQH4tSisUAP4+HEaHuMjGFRAN'.
                'qKnIxrGDP5e++w3/ASnkkJifGgB1vDDqeMf6jKCOpw9qMc7Goktm5BTUyQD6B2gw8lUnR0XYKOAlFCgJptwDWLPTtHVjmrCGFEvx'.
                'CEI+FAARTy2KyDOOWDDIxWckrXKScw+7TR5NVxtw+GS1zIGQQU4IHqhTjPMm8vUMvTfCohqBsxlx9eFfNc2pa5CySLH6iQFQ05nv'.
                'GRAc5zs0kmz3UjZn9e6IQL25CbtSb8oARoc4Y/CLWjJeBjTmArZbyibOL6GyrBJ7ErMyozbbomimiIRvF8Dfm/TdXV0NlwNHLfXQ'.
                'GAYo/LnHOJMmqxUbEm7IAGZOdIefG3luOUdLqOokqWVr0W0CLvy5Xpi9svaLnAv2/TRZeT8hHwBA3m/pNTjio67GsYCma2vHYymQ'.
                'I2GTv2/cdx2l5VVYMrsOrqpiSo+lde9mEFo/1NR74UhawsWp3/HUQkEhQuMjAVDZDaWOl9MnNFrF6Xq3eNwWgEK2mPUnkHYoG9l7'.
                'utzj1H0jAZHc3oYpJwVrd5k2xx8Q1tKs6V5CtgFQuF1/yhi6YLiL1xBA5dyaezYyHZHau62cmG7ClLmHqSULOPLTQIe26EhVMwBJ'.
                'iQRnQKP6DeRlfE+E5B8gZAsAKrtZ3sbhO3q8PJ2I56uElBkUq5TcCjfJOKVQNMtq+9OrZe3J47xpLXPIcfjJldLMGxqpnGAYgsry'.
                '6w8lJNeGeG8u8tDUp9AmTcpPHBMVvavkMwAcsR00arTKnCwO70WbwzgBsVP3tYtK52TzogTReyoR8ocHCMm1IV43UrIUtgZGpVYM'.
                'MuMq1gs09NFh9SYTFsYWyEsmhwcgOe412sqqdEPRKh9MEJhhXhmpYcHZHzVCINJ/SSieHsNHNhOSI+KFe/cbtd3XaPThzKdbM6PW'.
                'KN5zGsVzTicbp7aIF8IO4t/y1uNeKp9DxojcdhIbpUik9AkERCAANqsjOuSc5+swldUIsfFHN1OHXEeq5Rx5n9pvZNQ7zvwftOiu'.
                'I/TNXmsVz1UMhLMDgAFbEksRGX1cNh49LxirFgbLZwBs9QpHrPQuNLaCYKmh9gytHhbPCBzYtepyRAz/Camf5op26M3G0CiDXnsL'.
                '3F26WYsNSu7VWgcAvfLeDAB08Kj1SpWw6DCyMc4wz211DgAWJSIsBXbHrclOfPAcBot6IEqyYoUhHzfR+Y407swG3aGAoNCxXkET'.
                'oXZyRWc+oq0Bt4tTkZFxopIusPE0lcztXeQ0w8udWxjgww3oVOuO59wVe1VSlliY+peYTF9/Z3R3J2HdZBgJ3bWh62QM7IbELims'.
                'CooYAEokWD/1cIyaTgbAuhbrZrVsfKb/hh35PHcA/wFsTxbZyiyphgAAAABJRU5ErkJggg=='),
            'name' => 'icon.png',
        ]);
        $this->assertTrue($file->version->project->hasValidIcon());
    }

    /**
     * Check the size of content helper.
     */
    public function testFileSizeOfContentFormattedAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['content' => '']);
        $this->assertEquals('0 B', $file->version->project->size_of_content_formatted);
        $file = factory(File::class)->create(['content' => '321']);
        $this->assertEquals('3 B', $file->version->project->size_of_content_formatted);
        $file = factory(File::class)->create(['content' => $this->faker->regexify('[A-Za-z0-9]{1024}')]);
        $this->assertEquals('1 KiB', $file->version->project->size_of_content_formatted);
    }

    /**
     * Check the size of zip helper.
     */
    public function testFileSizeOfZipFormattedAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertEquals('0 B', $project->size_of_zip_formatted);
    }

    /**
     * Check that a Vote has existing type.
     */
    public function testProjectsVersionFilesUnderscoreUnderscoreInitUnderscoreUnderscoreDotPy(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create();
        /** @var Version $version */
        $version = $project->versions->last();
        $this->assertCount(1, $version->files);
        /** @var File $file */
        $file = $version->files->last();
        $this->assertEquals('__init__.py', $file->name);
    }

    /**
     * Check that a Vote has existing type.
     */
    public function testProjectsGitEmptyVersionFiles(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Project $project */
        $project = factory(Project::class)->create(['git' => 'https://github.com/badgeteam/Hatchery']);
        /** @var Version $version */
        $version = $project->versions->last();
        $this->assertEmpty($version->files);
    }

    /**
     * Assert the Project has a relation with a collaborator Users.
     */
    public function testProjectUsersRelationship(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertEmpty($project->collaborators);
        $project->collaborators()->attach($otherUser);
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertInstanceOf(Collection::class, $project->collaborators);
        $this->assertInstanceOf(User::class, $project->collaborators->first());
        /** @var User $collaborator */
        $collaborator = $project->collaborators->first();
        $this->assertEquals($otherUser->id, $collaborator->id);
        $this->assertCount(1, $project->collaborators);
        $anotherUser = factory(User::class)->create();
        $project->collaborators()->attach($anotherUser);
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertCount(2, $project->collaborators);
    }

    /**
     * Let's catch an event :).
     */
    public function testProjectUpdateEventUser(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();

        Event::fake();

        event(new ProjectUpdated($project, $this->faker->text));

        Event::assertDispatched(ProjectUpdated::class, function ($e) use ($project) {
            return $e->project->id === $project->id;
        }, 1);
    }

    /**
     * Let's catch some events :).
     */
    public function testProjectUpdateEventCollaborator(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $otherUser = factory(User::class)->create();
        $project->collaborators()->attach($otherUser);

        Event::fake();

        event(new ProjectUpdated($project, $this->faker->text));

        Event::assertDispatched(ProjectUpdated::class, function ($e) use ($project) {
            return $e->project->id === $project->id;
        }, 2);
    }

    /**
     * Let's not catch some events :).
     */
    public function testProjectUpdateEventCollaboratorRecepients(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $otherUser = factory(User::class)->create();
        $project->collaborators()->attach($otherUser);

        $event = new ProjectUpdated($project, $this->faker->text);

        $this->assertCount(2, $event->broadcastOn());
        $this->assertEquals('private-App.User.'.$user->id, $event->broadcastOn()[0]->name);
        $this->assertEquals('private-App.User.'.$otherUser->id, $event->broadcastOn()[1]->name);
    }
}
