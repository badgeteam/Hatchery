<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

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
     * The published egg archive under a stable, guessable URL (#137).
     */
    public function testMchArchive(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);

        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var Version $version */
        $version = Version::factory()->create();
        $version->project->badges()->attach($badge);
        /** @var Category $category */
        $category = $version->project->category()->first();

        $base = '/v2/' . $badge->slug . '/python/' . $category->slug . '/' . $version->project->slug;

        // Nothing published yet.
        $this->json('GET', $base . '.tar.gz')->assertStatus(404);

        // Published, but the archive is not on disk.
        $version->zip = 'eggs/missing_egg.tar.gz';
        $version->save();
        $this->json('GET', $base . '.tar.gz')->assertStatus(404);

        // Published and present.
        $relative = 'eggs/' . $version->project->slug . '_test.tar.gz';
        $path = public_path($relative);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, gzencode('not really a tar, but bytes are bytes'));
        $version->zip = $relative;
        $version->save();

        $response = $this->call('GET', $base . '.tar.gz');
        $response->assertStatus(200);
        $this->assertEquals('application/gzip', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            $version->project->slug . '.tar.gz',
            (string) $response->headers->get('Content-Disposition')
        );

        unlink($path);
    }

    /**
     * An unknown egg has no archive either.
     */
    public function testMchArchiveUnknownApp(): void
    {
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create();

        $this->json('GET', '/v2/' . $badge->slug . '/python/' . $category->slug . '/nope.tar.gz')
            ->assertStatus(404);
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
            'size' => $file->size_of_content,
            'crc32' => $file->crc32
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

    /**
     * The MCH2022 badge only allocates a download buffer when it sees a
     * Content-Length header, so a chunked reply is a reply it cannot read.
     */
    public function testMchJsonRepliesCarryContentLength(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();

        foreach (['/v2/devices', '/v2/' . $badge->slug . '/types'] as $url) {
            $response = $this->json('GET', $url);
            $response->assertStatus(200);
            $this->assertNotNull(
                $response->headers->get('Content-Length'),
                $url . ' must send a Content-Length header'
            );
            $this->assertSame(
                strlen((string) $response->getContent()),
                (int) $response->headers->get('Content-Length'),
                $url . ' must send the length of what it actually sent'
            );
        }
    }
}
