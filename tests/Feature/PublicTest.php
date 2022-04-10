<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class PublicTest.
 *
 * @author annejan@badge.team
 */
class PublicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function testWelcome(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200)
            ->assertViewHas('badge', '')
            ->assertViewHas('category', '')
            ->assertViewHas('users', User::count())
            ->assertViewHas('projects', Project::count());
    }

    /**
     * A basic test example with a Badge.
     */
    public function testWelcomeBadge(): void
    {
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $response = $this->get('/?badge=' . $badge->slug);
        $response->assertStatus(200)
            ->assertViewHas('badge', $badge->slug)
            ->assertViewHas('category', '')
            ->assertViewHas('users', User::count())
            ->assertViewHas('projects', Project::count());
    }

    /**
     * A basic test example with sorting.
     */
    public function testWelcomeOrder(): void
    {
        $response = $this->get('/?order=published_at');
        $response->assertStatus(200)
            ->assertViewHas('order', 'published_at')
            ->assertViewHas('direction', 'desc')
            ->assertViewHas('badge', '')
            ->assertViewHas('category', '')
            ->assertViewHas('users', User::count())
            ->assertViewHas('projects', Project::count());
    }

    /**
     * A basic test example with sorting direction.
     */
    public function testWelcomeOrderAsc(): void
    {
        $response = $this->get('/?order=name&direction=asc');
        $response->assertStatus(200)
            ->assertViewHas('order', 'name')
            ->assertViewHas('direction', 'asc')
            ->assertViewHas('badge', '')
            ->assertViewHas('category', '')
            ->assertViewHas('users', User::count())
            ->assertViewHas('projects', Project::count());
    }

    /**
     * A basic test example with a Category.
     */
    public function testWelcomeCategory(): void
    {
        /** @var Category $category */
        $category = Category::factory()->create();
        $response = $this->get('/?category=' . $category->slug);
        $response->assertStatus(200)
            ->assertViewHas('badge', '')
            ->assertViewHas('category', $category->slug)
            ->assertViewHas('users', User::count())
            ->assertViewHas('projects', Project::count());
    }

    /**
     * A basic test example with Badge and Category.
     */
    public function testWelcomeBadgeCategory(): void
    {
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create();
        $response = $this->get('/?badge=' . $badge->slug . '&category=' . $category->slug);
        $response->assertStatus(200)
            ->assertViewHas('badge', $badge->slug)
            ->assertViewHas('category', $category->slug)
            ->assertViewHas('users', User::count())
            ->assertViewHas('projects', Project::count());
    }

    /**
     * A badge test example.
     */
    public function testBadge(): void
    {
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $response = $this->get('/badge/' . $badge->slug);
        $response->assertStatus(200)
            ->assertViewHas('badge', $badge->slug)
            ->assertViewHas('users', User::count())
            ->assertViewHas('projects', Project::count())
            ->assertViewHas('category', '');
    }

    /**
     * A badge category example.
     */
    public function testBadgeCategory(): void
    {
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create();
        $response = $this->get('/badge/' . $badge->slug . '?category=' . $category->slug);
        $response->assertStatus(200)
            ->assertViewHas('badge', $badge->slug)
            ->assertViewHas('users', User::count())
            ->assertViewHas('projects', Project::count())
            ->assertViewHas('category', $category->slug);
    }

    /**
     * Check redirect to /login when going to the /home page.
     */
    public function testHomeRedirect(): void
    {
        $response = $this->get('/home');
        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * Check JSON request Unauthenticated . .
     */
    public function testJsonRedirect(): void
    {
        $response = $this->json('GET', '/home');
        $response->assertStatus(401)
            ->assertExactJson(['error' => 'Unauthenticated.']);
    }

    /**
     * Check JSON egg request . .
     */
    public function testProjectGetJsonModelNotFound(): void
    {
        $response = $this->json('GET', '/eggs/get/something/json');
        $response->assertStatus(404)
            ->assertExactJson(['message' => 'No releases found']);
    }

    /**
     * Check JSON egg request . .
     */
    public function testProjectGetJson(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/eggs/get/' . $version->project->slug . '/json');
        $response->assertStatus(200)
            ->assertExactJson([
                'name'        => $version->project->name,
                'description' => null,
                'info'        => ['version' => '1'],
                'category'    => $category->slug,
                'releases'    => [
                    '1' => [
                        [
                            'url' => url('some_path.tar.gz'),
                        ],
                    ],
                ],
                'min_firmware' => null,
                'max_firmware' => null,
            ]);
    }

    /**
     * Check JSON egg request . .
     */
    public function testProjectGetJsonDescription(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/eggs/get/' . $version->project->slug . '/json?description=true');
        $response->assertStatus(200)
            ->assertExactJson([
                'description' => null,
                'name'        => $version->project->name,
                'info'        => ['version' => '1'],
                'category'    => $category->slug,
                'releases'    => [
                    '1' => [
                        [
                            'url' => url('some_path.tar.gz'),
                        ],
                    ],
                ],
                'min_firmware' => null,
                'max_firmware' => null,
            ]);
    }

    /**
     * Check JSON egg request . .
     */
    public function testProjectGetJson404(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();

        $response = $this->json('GET', '/eggs/get/' . $version->project->slug . '/json');
        $response->assertStatus(404)
            ->assertExactJson(['message' => 'No releases found']);
    }

    /**
     * Check JSON eggs request . .
     */
    public function testProjectListJson(): void
    {
        $response = $this->json('GET', '/eggs/list/json');
        $response->assertStatus(200)->assertExactJson([]);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        File::factory()->create(['version_id' => $version->id]);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/eggs/list/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'author'                  => $version->project->user->name,
                    'name'                    => $version->project->name,
                    'description'             => null,
                    'revision'                => '1',
                    'slug'                    => $version->project->slug,
                    'size_of_content'         => $version->project->size_of_content,
                    'size_of_zip'             => 0,
                    'category'                => $category->slug,
                    'download_counter'        => 0,
                    'status'                  => 'unknown',
                    'published_at'            => null,
                    'min_firmware'            => null,
                    'max_firmware'            => null,
                ],
            ]);
    }

    /**
     * Check JSON eggs request . .
     */
    public function testProjectSearchJson(): void
    {
        $response = $this->json('GET', '/eggs/search/something/json');
        $response->assertStatus(200)->assertExactJson([]);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();

        $len = strlen($version->project->name);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/eggs/search/' .
            substr($version->project->name, 2, $len - 4) . '/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'author'                  => $version->project->user->name,
                    'name'                    => $version->project->name,
                    'description'             => null,
                    'revision'                => '1',
                    'slug'                    => $version->project->slug,
                    'size_of_content'         => 0,
                    'size_of_zip'             => 0,
                    'category'                => $category->slug,
                    'download_counter'        => 0,
                    'status'                  => 'unknown',
                    'published_at'            => null,
                    'min_firmware'            => null,
                    'max_firmware'            => null,
                ],
            ]);
    }

    /**
     * Check JSON eggs request . .
     */
    public function testProjectCategoryJson(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/eggs/category/nonexisting/json');
        $response->assertStatus(404);

        $response = $this->json('GET', '/eggs/category/' . $category->slug . '/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'author'                  => $version->project->user->name,
                    'name'                    => $version->project->name,
                    'description'             => null,
                    'revision'                => '1',
                    'slug'                    => $version->project->slug,
                    'size_of_content'         => 0,
                    'size_of_zip'             => 0,
                    'category'                => $category->slug,
                    'download_counter'        => 0,
                    'status'                  => 'unknown',
                    'published_at'            => null,
                    'min_firmware'            => null,
                    'max_firmware'            => null,
                ],
            ]);
    }

    /**
     * Check JSON eggs request . .
     */
    public function testCategoriesJson(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->project->save();
        $version->save();
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/eggs/categories/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'eggs' => 1,
                ],
            ]);
    }

    /**
     * Check JSON eggs request . .
     */
    public function testCategoriesCountJson(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'iets anders';
        $version->project->save();
        $version->save();
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/eggs/categories/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'eggs' => 1,
                ],
            ]);
    }

    /**
     * Check JSON eggs request . .
     */
    public function testCategoriesUnpublishedJson(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->project->save();
        $version->save();
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/eggs/categories/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'eggs' => 0,
                ],
            ]);
    }

    /**
     * Check public project view.
     */
    public function testProjectShow(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();

        $response = $this->get('/projects/' . $version->project->slug . '');
        $response->assertStatus(200)
            ->assertViewHas('project');
    }

    /**
     * Check public file view.
     */
    public function testFileShow(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();

        $response = $this->get('/files/' . $file->id . '');
        $response->assertStatus(200)
            ->assertViewHas('file');
    }

    /**
     * Check JSON basket request . .
     */
    public function testBasketListJson(): void
    {
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        $version->project->badges()->attach($badge);
        File::factory()->create(['version_id' => $version->id]);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/basket/nonexisting/list/json');
        $response->assertStatus(404);

        $response = $this->json('GET', '/basket/' . $badge->slug . '/list/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'author'                  => $version->project->user->name,
                    'name'                    => $version->project->name,
                    'description'             => null,
                    'revision'                => '1',
                    'slug'                    => $version->project->slug,
                    'size_of_content'         => $version->project->size_of_content,
                    'size_of_zip'             => 0,
                    'category'                => $category->slug,
                    'download_counter'        => 0,
                    'status'                  => 'unknown',
                    'published_at'            => null,
                    'min_firmware'            => null,
                    'max_firmware'            => null,
                ],
            ]);
    }

    /**
     * Check JSON basket request . .
     */
    public function testBasketSearchJson(): void
    {
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $response = $this->json('GET', '/eggs/search/something/json');
        $response->assertStatus(200)->assertExactJson([]);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        $version->project->badges()->attach($badge);

        $len = strlen($version->project->name);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/basket/nonexisting/search/' .
            substr($version->project->name, 2, $len - 4) . '/json');
        $response->assertStatus(404);

        $response = $this->json('GET', '/basket/' . $badge->slug . '/search/' .
            substr($version->project->name, 2, $len - 4) . '/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'author'                  => $version->project->user->name,
                    'name'                    => $version->project->name,
                    'description'             => null,
                    'revision'                => '1',
                    'slug'                    => $version->project->slug,
                    'size_of_content'         => 0,
                    'size_of_zip'             => 0,
                    'category'                => $category->slug,
                    'download_counter'        => 0,
                    'status'                  => 'unknown',
                    'published_at'            => null,
                    'min_firmware'            => null,
                    'max_firmware'            => null,
                ],
            ]);
    }

    /**
     * Check JSON basket request . .
     */
    public function testBasketCategoriesJson(): void
    {
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        $version->project->badges()->attach($badge);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/basket/nonexisting/categories/json');
        $response->assertStatus(404);

        $response = $this->json('GET', '/basket/' . $badge->slug . '/categories/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'eggs' => 1,
                ],
            ]);

        Category::factory()->create();

        $this->assertCount(2, Category::all());

        $response = $this->json('GET', '/basket/' . $badge->slug . '/categories/json');
        $response->assertExactJson([
            [
                'name' => $category->name,
                'slug' => $category->slug,
                'eggs' => 1,
            ],
        ]);
    }

    /**
     * Check JSON basket request . .
     */
    public function testBasketCategoryJson(): void
    {
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        $version->project->badges()->attach($badge);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/basket/nonexisting/category/' . $category->slug . '/json');
        $response->assertStatus(404);

        $response = $this->json('GET', '/basket/' . $badge->slug . '/category/' . $category->slug . '/json');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'author'           => $version->project->user->name,
                    'name'             => $version->project->name,
                    'description'      => null,
                    'revision'         => '1',
                    'slug'             => $version->project->slug,
                    'size_of_content'  => 0,
                    'size_of_zip'      => 0,
                    'category'         => $category->slug,
                    'download_counter' => 0,
                    'status'           => 'unknown',
                    'published_at'     => null,
                    'min_firmware'     => null,
                    'max_firmware'     => null,
                ],
            ]);
    }

    /**
     * Check JSON egg request . .
     */
    public function testProjectGetJsonMinMaxFirmware(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        /** @var Category $category */
        $category = $version->project->category()->first();
        $version->project->min_firmware = 13;
        $version->project->max_firmware = 37;
        $version->project->save();

        $response = $this->json('GET', '/eggs/get/' . $version->project->slug . '/json');
        $response->assertStatus(200)
            ->assertExactJson([
                'name'        => $version->project->name,
                'description' => null,
                'info'        => ['version' => '1'],
                'category'    => $category->slug,
                'releases'    => [
                    '1' => [
                        [
                            'url' => url('some_path.tar.gz'),
                        ],
                    ],
                ],
                'min_firmware' => 13,
                'max_firmware' => 37,
            ]);
    }

    /**
     * Check redirect to /login when going to the /horizon page.
     */
    public function testHorizonRedirect(): void
    {
        $response = $this->get('/horizon');
        $response->assertStatus(302)
            ->assertRedirect('/login');
    }
}
