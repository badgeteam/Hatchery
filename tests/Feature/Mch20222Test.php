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
class Mch20222Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Simple list
     */
    public function testMchDevices(): void
    {
        $response = $this->json('GET', '/mch2022/devices');
        $response->assertStatus(200)
            ->assertExactJson([]);

        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();

        $response = $this->json('GET', '/mch2022/devices');
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
    public function testMchCategories(): void
    {
        $response = $this->json('GET', '/mch2022/iets/esp32/categories');
        $response->assertStatus(404);

        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();

        $response = $this->json('GET', '/mch2022/' . $badge->slug . '/esp32/categories');
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

        $response = $this->json('GET', '/mch2022/' . $badge->slug . '/esp32/categories');
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
     * Check JSON files request . .
     */
    public function testProjectFilesGetJsonModelNotFound(): void
    {
        $response = $this->json('GET', '/eggs/files/something/json');
        $response->assertStatus(404)
            ->assertExactJson(['message' => 'Project not found']);
    }

    /**
     * Check JSON files request . .
     */
    public function testProjectFilesGetJsonFilesNotFound(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        $response = $this->json('GET', '/eggs/files/' . $project->slug . '/json');
        $response->assertStatus(404)
            ->assertExactJson(['message' => 'No files found']);
    }

    /**
     * Check JSON files request . .
     */
    public function testProjectFilesGetJson(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();

        $version = $file->version;
        $version->zip = 'some_path.tar.gz';
        $version->save();

        $response = $this->json('GET', '/eggs/files/' . $version->project->slug . '/json');
        $response->assertStatus(200)
            ->assertJson([
                [
                    'name' => $file->name,
                    'size' => $file->size_of_content,
                    'extension' => $file->extension
                ]
            ]);
    }

    /**
     * Check File request . .
     */
    public function testProjectFileContentModelNotFound(): void
    {
        $response = $this->json('GET', '/eggs/file/something/get/file.py');
        $response->assertStatus(404)
            ->assertExactJson(['message' => 'Project not found']);
    }

    /**
     * Check File request . .
     */
    public function testProjectFileContentFilesNotFound(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        $response = $this->json('GET', '/eggs/file/' . $project->slug . '/get/file.py');
        $response->assertStatus(404)
            ->assertExactJson(['message' => 'No files found']);
    }

    /**
     * Check File request . .
     */
    public function testProjectFileContentFileNotFound(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();

        $version = $file->version;
        $version->zip = 'some_path.tar.gz';
        $version->save();

        $response = $this->json('GET', '/eggs/file/' . $version->project->slug . '/get/file.py');
        $response->assertStatus(404)
            ->assertExactJson(['message' => 'File not found']);
    }

    /**
     * Check File request . .
     */
    public function testProjectFileContent(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();

        $version = $file->version;
        $version->zip = 'some_path.tar.gz';
        $version->save();

        $response = $this->json('GET', '/eggs/file/' . $version->project->slug . '/get/' . $file->name);
        $response->assertStatus(200)
            ->assertHeader('Content-Type', $file->mime)
            ->assertSee($file->content);
    }
}
