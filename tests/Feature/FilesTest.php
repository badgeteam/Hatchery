<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace Tests\Feature;

use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Support\Icon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class FilesTest.
 *
 * @author annejan@badge.team
 */
class FilesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testUploadFile(): void
    {
        $stub = __DIR__ . '/heart.png';
        $name = Str::random(8) . '.png';
        $path = sys_get_temp_dir() . '/' . $name;
        copy($stub, $path);
        $file = new UploadedFile($path, $name, 'image/png', null, true);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Version $lastVer */
        $lastVer = $project->versions->last();
        $response = $this
            ->actingAs($user)
            ->post('/upload/' . $lastVer->id, ['file' => $file]);
        $response->assertStatus(200);

        $this->assertCount(2, File::all()); // you get a free __init__.py
        /** @var File $file */
        $file = File::first();
        $this->assertEquals('__init__.py', $file->name);
        /** @var File $file */
        $file = File::where('name', '!=', '__init__.py')->first();
        $this->assertEquals($name, $file->name);
    }

    /**
     * A badge needs icon.png at exactly 32 by 32, so an upload of any other
     * size is scaled to fit rather than silently left out of the egg (#126).
     */
    public function testUploadIconIsResizedToBadgeSize(): void
    {
        $stub = __DIR__ . '/heart.png';
        $path = sys_get_temp_dir() . '/' . Str::random(8) . '.png';
        copy($stub, $path);

        // The fixture is deliberately not 32 by 32.
        $this->assertNotEquals([Icon::SIZE, Icon::SIZE], Icon::dimensions((string) file_get_contents($stub)));

        $upload = new UploadedFile($path, Icon::NAME, 'image/png', null, true);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Version $lastVer */
        $lastVer = $project->versions->last();

        $this->actingAs($user)
            ->post('/upload/' . $lastVer->id, ['file' => $upload])
            ->assertStatus(200);

        /** @var File $icon */
        $icon = File::where('name', Icon::NAME)->firstOrFail();
        $this->assertEquals([Icon::SIZE, Icon::SIZE], Icon::dimensions((string) $icon->content));
        $this->assertTrue($icon->isValidIcon());
        $this->assertTrue($project->refresh()->hasValidIcon());
    }

    /**
     * A file at the advertised ceiling has to survive the whole chain: PHP's
     * upload limits, MariaDB's max_allowed_packet, and the column it is stored
     * in, which used to be a 16 MiB MEDIUMBLOB (#127).
     */
    public function testUploadLargeFile(): void
    {
        $megabytes = 20;
        $path = sys_get_temp_dir() . '/' . Str::random(8) . '.bin';
        file_put_contents($path, str_repeat(random_bytes(1024), $megabytes * 1024));
        $this->assertGreaterThan(16 * 1024 * 1024, filesize($path));

        $upload = new UploadedFile($path, 'big.bin', 'application/octet-stream', null, true);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Version $lastVer */
        $lastVer = $project->versions->last();

        $this->actingAs($user)
            ->post('/upload/' . $lastVer->id, ['file' => $upload])
            ->assertStatus(200);

        /** @var File $stored */
        $stored = File::where('name', 'big.bin')->firstOrFail();
        $this->assertEquals(filesize($path), strlen((string) $stored->content));

        unlink($path);
    }

    /**
     * Beyond the ceiling the user gets a validation error rather than a bare
     * 413 from the web server.
     */
    public function testUploadRejectsFilesOverTheLimit(): void
    {
        // fake()->create declares the size without writing the bytes.
        $upload = UploadedFile::fake()->create('too-big.bin', (File::MAX_UPLOAD_MEGABYTES * 1024) + 1024);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Version $lastVer */
        $lastVer = $project->versions->last();

        $this->actingAs($user)
            ->post('/upload/' . $lastVer->id, ['file' => $upload])
            ->assertSessionHasErrors('file');

        $this->assertNull(File::where('name', 'too-big.bin')->first());
    }

    /**
     * Only the badge icon is touched; other images are stored as uploaded.
     */
    public function testUploadLeavesOtherImagesAlone(): void
    {
        $stub = __DIR__ . '/heart.png';
        $original = (string) file_get_contents($stub);
        $path = sys_get_temp_dir() . '/' . Str::random(8) . '.png';
        copy($stub, $path);

        $upload = new UploadedFile($path, 'splash.png', 'image/png', null, true);
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Version $lastVer */
        $lastVer = $project->versions->last();

        $this->actingAs($user)
            ->post('/upload/' . $lastVer->id, ['file' => $upload])
            ->assertStatus(200);

        /** @var File $stored */
        $stored = File::where('name', 'splash.png')->firstOrFail();
        $this->assertEquals($original, $stored->content);
    }

//    public function testUploadIllegalFile(): void
//    {
//        $stub = __DIR__.'/empty.zip';
//        $name = Str::random(8).'.zip';
//        $path = sys_get_temp_dir().'/'.$name;
//        copy($stub, $path);
//        $file = new UploadedFile($path, $name, 'applications/zip', null, true);
//        $user = User::factory()->create();
//        $this->be($user);
//        $project = Project::factory()->create();
//
//        $response = $this
//            ->actingAs($user)
//            ->post('/upload/'.$project->versions->last()->id, ['file' => $file]);
//        $response->assertStatus(302);
//    }

    /**
     * Check the files edit page functions.
     */
    public function testFilesEdit(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/files/' . $file->id . '/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the files edit page functions for other users.
     */
    public function testFilesEditOtherUser(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $response = $this
            ->actingAs($otherUser)
            ->get('/files/' . $file->id . '/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the files edit page functions for other users.
     */
    public function testFilesEditCollaboratingUser(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $file->version->project->collaborators()->attach($otherUser);
        $response = $this
            ->actingAs($otherUser)
            ->get('/files/' . $file->id . '/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the files edit page doesn't work for git projects.
     */
    public function testFilesEditGit(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $file->version->project->git = 'some.uri';
        $file->version->project->save();
        $response = $this
            ->actingAs($user)
            ->get('/files/' . $file->id . '/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdate(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $data = 'import time
time.localtime()';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertRedirect('/projects/' . $file->version->project->slug . '/edit')
            ->assertSessionHas('successes');
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertEquals($data, $file->content);
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdateNonPy(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'info.txt']);
        $data = 'info';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertRedirect('/projects/' . $file->version->project->slug . '/edit')
            ->assertSessionHas('successes');
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertEquals($data, $file->content);
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdateMarkdown(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'README.md']);
        $data = '# test

text';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertRedirect('/projects/' . $file->version->project->slug . '/edit')
            ->assertSessionHas('successes')
            ->assertSessionHasNoErrors();
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertEquals($data, $file->content);
    }

    /**
     * Check the files can be stored.
     *
     * @throws \JsonException
     */
    public function testFilesUpdateJson(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.json']);
        $data = json_encode(['tests' => ['test1', 'test2']], JSON_THROW_ON_ERROR);
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertRedirect('/projects/' . $file->version->project->slug . '/edit')
            ->assertSessionHas('successes')
            ->assertSessionHasNoErrors();
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertEquals($data, $file->content);
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdateVerilog(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.v']);
        $data = '`default_nettype none
module chip (
  output  O_LED_R
  );
  wire  w_led_r;
  assign w_led_r = 1\'b0;
  assign O_LED_R = w_led_r;
endmodule';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertRedirect('/projects/' . $file->version->project->slug . '/edit')
            ->assertSessionHas('successes')
            ->assertSessionHasNoErrors();
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertEquals($data, $file->content);
    }

    /**
     * Check the files can't be stored by other users.
     */
    public function testFilesUpdateOtherUser(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $data = 'import time
time.localtime()';
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertStatus(403);
    }

    /**
     * Check the files can't be stored by other users.
     */
    public function testFilesUpdateCollaboratingUser(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $file->version->project->collaborators()->attach($otherUser);
        $data = 'import time
time.localtime()';
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertStatus(302);
    }

    /**
     * Check the files can't be updated when project uses git.
     */
    public function testFilesUpdateGit(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $file->version->project->git = 'some.uri';
        $file->version->project->save();
        $data = 'import time
time.localtime()';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertStatus(403);
    }

    /**
     * Check the files can be deleted.
     */
    public function testFilesDestroy(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $response = $this
            ->actingAs($user)
            ->call('delete', '/files/' . $file->id);
        $response->assertRedirect('/projects/' . $file->version->project->slug . '/edit')
            ->assertSessionHas('successes');
    }

    /**
     * Check the files can't be deleted from git managed project.
     */
    public function testFilesDestroyGit(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $file->version->project->git = 'some.uri';
        $file->version->project->save();
        $response = $this
            ->actingAs($user)
            ->call('delete', '/files/' . $file->id);
        $response->assertStatus(403);
    }

    /**
     * Check the files create page functions.
     */
    public function testFilesCreate(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/files/create?version=' . $version->id);
        $response->assertStatus(200)
            ->assertViewHas('version', Version::find($version->id));
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesStore(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $response = $this
            ->actingAs($user)
            ->post('/files', ['name' => 'test.py', 'file_content' => '# test', 'version_id' => $version->id]);
        $response->assertRedirect('/projects/' . $version->project->slug . '/edit')
            ->assertSessionHas('successes');
        /** @var File $file */
        $file = File::all()->last();
        $this->assertEquals('test.py', $file->name);
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesStoreNameTooLarge(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $response = $this
            ->actingAs($user)
            ->post(
                '/files',
                [
                    'name' => $this->faker->text(1024), 'file_content' => '# test',
                    'version_id' => $version->id
                ]
            );
        $response->assertRedirect('/files/create')
            ->assertSessionHasErrors();
    }

    /**
     * Check the files can be viewed (publicly).
     */
    public function testFilesView(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $response = $this
            ->call('get', '/files/' . $file->id);
        $response->assertStatus(200)->assertViewHas(['file']);
    }

    /**
     * Check the files can be downloaded (publicly).
     */
    public function testFilesDownload(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $response = $this
            ->call('get', '/download/' . $file->id);
        $response->assertStatus(200)->assertHeader('Content-Type', 'application/x-python-code');
    }

    /**
     * Check the files create icon magic page functions.
     */
    public function testFilesCreateIcon(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/create-icon?version=' . $version->id);
        $response->assertRedirect()
            ->assertSessionHas('successes');
        /** @var File $file */
        $file = File::get()->last();
        $this->assertEquals('icon.py', $file->name);
        $this->assertEquals('icon = ([0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000,' .
            ' 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000,' .
            ' 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000,' .
            ' 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000,' .
            ' 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000,' .
            ' 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000,' .
            ' 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000,' .
            ' 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000, 0x00000000,' .
            ' 0x00000000, 0x00000000], 1)', $file->content);
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesCreateIconNameTooLarge(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Version $version */
        $version = Version::factory()->create();
        $response = $this
            ->actingAs($user)
            ->post('/create-icon?version=' . $version->id, ['name' => $this->faker->text(1024)]);
        $response->assertRedirect('/projects/' . $version->project->slug . '/edit')
            ->assertSessionHasErrors();
    }
}
