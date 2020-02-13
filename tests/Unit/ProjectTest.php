<?php

namespace Tests\Unit;

use App\Models\Badge;
use App\Models\BadgeProject;
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
    use DatabaseTransactions;
    use DatabaseMigrations;

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
        $this->assertEquals("<h2>Description</h2>\n", $file->version->project->descriptionHtml);
    }

    /**
     * Test the userVoted helper.
     */
    public function testProjectUserVoted()
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
    public function testProjectStatusMagic()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $this->assertEquals('unknown', $project->status);
        $badge = factory(Badge::class)->create();
        $project->badges()->attach($badge);
        $state = BadgeProject::where('badge_id', $badge->id)->where('project_id', $project->id)->first();
        $state->status = 'broken';
        $state->save();
        $project = Project::find($project->id);
        $this->assertEquals('broken', $project->status);
        $badge = factory(Badge::class)->create();
        $project->badges()->attach($badge);
        $state = BadgeProject::where('badge_id', $badge->id)->where('project_id', $project->id)->first();
        $state->status = 'in_progress';
        $state->save();
        $project = Project::find($project->id);
        $this->assertEquals('in_progress', $project->status);
        $badge = factory(Badge::class)->create();
        $project->badges()->attach($badge);
        $state = BadgeProject::where('badge_id', $badge->id)->where('project_id', $project->id)->first();
        $state->status = 'working';
        $state->save();
        $project = Project::find($project->id);
        $this->assertEquals('working', $project->status);
        $badge = factory(Badge::class)->create();
        $project->badges()->attach($badge);
        $state = BadgeProject::where('badge_id', $badge->id)->where('project_id', $project->id)->first();
        $state->status = 'in_progress';
        $state->save();
        $project = Project::find($project->id); // stay at working
        $this->assertEquals('working', $project->status);
        $this->assertCount(4, BadgeProject::all());
    }

    /**
     * Test the hasValidIcon() helper.
     */
    public function testProjectHasValidIcon()
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
}
