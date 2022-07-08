<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Category;
use App\Models\File;
use App\Models\User;
use App\Models\Version;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class PublicTest.
 *
 * @author annejan@badge.team
 */
class Mch20222Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Simple list
     */
    public function testMchDevices(): void
    {
        $response = $this->json('GET', '/v2/devices');
        $response->assertStatus(200)
            ->assertExactJson([]);

        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();

        $response = $this->json('GET', '/v2/devices');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'name' => $badge->name,
                    'slug' => $badge->slug,
                ]
            ]);
    }

    /**
     * Simple list
     */
    public function testMchDeviceTypes(): void
    {
        $response = $this->json('GET', '/v2/random_device/types');
        $response->assertStatus(404);

        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();

        $response = $this->json('GET', '/v2/' . $badge->slug . '/types');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'name' => 'ESP32 native binaries',
                    'slug' => 'esp32',
                ],
                [
                    'name' => 'Micropython eggs',
                    'slug' => 'python',
                ],
                [
                    'name' => 'ICE40 FPGA bitstreams',
                    'slug' => 'ice40',
                ],
            ]);
    }

    /**
     * List categories for badge / type
     */
    public function testMchCategories(): void
    {
        $response = $this->json('GET', '/v2/iets/esp32/categories');
        $response->assertStatus(404);

        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();

        $response = $this->json('GET', '/v2/' . $badge->slug . '/esp32/categories');
        $response->assertStatus(200)
            ->assertExactJson([]);

        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        $version->project->badges()->attach($badge);
        File::factory()->create(['version_id' => $version->id]);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/v2/' . $badge->slug . '/esp32/categories');
        $response->assertStatus(200)
            ->assertExactJson([]);

        $response = $this->json('GET', '/v2/' . $badge->slug . '/python/categories');
        $response->assertStatus(200)
            ->assertExactJson([
                [
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'apps' => 1
                ]
            ]);
    }

    /**
     * Check JSON files / app info request . .
     */
    public function testMchApps(): void
    {
        $response = $this->json('GET', '/v2/iets/app/some_app');
        $response->assertStatus(404);

        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);

        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();
        $version->project->badges()->attach($badge);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json('GET', '/v2/' . $badge->slug . '/python/' . $category->slug .  '/iets');
        $response->assertStatus(404);

        $response = $this->json(
            'GET',
            '/v2/' . $badge->slug . '/python/' . $category->slug .  '/' . $version->project->slug
        );
        $response->assertStatus(200)
            ->assertJson([]);
        /** @var File $file */
        $file = File::factory()->create(['version_id' => $version->id]);
        $response = $this->json(
            'GET',
            '/v2/' . $badge->slug . '/python/' . $category->slug .  '/' . $version->project->slug
        );
        $response->assertStatus(200)
            ->assertExactJson([
                'device' => $badge->slug,
                'type' => 'python',
                'category' => $category->slug,
                'slug' => $version->project->slug,
                'name' => $version->project->name,
                'author' => $version->project->author,
                'license' => $version->project->license,
                'description' => $version->project->description,
                'version' => (int)$version->project->revision,
                'files' => [
                    [
                        'name' => $file->name,
                        'url' => url(
                            'v2/' . $badge->slug . '/python/' . $category->slug .  '/' .
                            $version->project->slug . '/' . $file->name
                        ),
                        'size' => $file->size_of_content
                    ]
                ]
            ]);
    }

    /**
     * Check File request . .
     */
    public function testMchFile(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var File $file */
        $file = File::factory()->create();

        $version = $file->version;
        $version->zip = 'some_path.tar.gz';
        $version->save();
        $version->project->badges()->attach($badge);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $response = $this->json(
            'GET',
            '/v2/' . $badge->slug . '/python/' . $category->slug .  '/' .
            $version->project->slug . '/random.txt'
        );
        $response->assertStatus(404)
            ->assertExactJson(['message' => 'File not found']);

        $response = $this->json(
            'GET',
            '/v2/' . $badge->slug . '/python/' . $category->slug .  '/' .
            $version->project->slug . '/' . $file->name
        );
        $response->assertStatus(200)
            ->assertHeader('Content-Type', $file->mime)
            ->assertSee($file->content);
    }
}
