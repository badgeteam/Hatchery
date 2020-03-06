<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
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
        $stub = __DIR__.'/heart.png';
        $name = Str::random(8).'.png';
        $path = sys_get_temp_dir().'/'.$name;
        copy($stub, $path);
        $file = new UploadedFile($path, $name, 'image/png', null, true);
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();

        $response = $this
            ->actingAs($user)
            ->post('/upload/'.$project->versions->last()->id, ['file' => $file]);
        $response->assertStatus(200);

        $this->assertCount(2, File::all()); // you get a free __init__.py
        /** @var File $file */
        $file = File::first();
        $this->assertEquals('__init__.py', $file->name);
        /** @var File $file */
        $file = File::where('name', '!=', '__init__.py')->first();
        $this->assertEquals($name, $file->name);
    }

//    public function testUploadIllegalFile(): void
//    {
//        $stub = __DIR__.'/empty.zip';
//        $name = Str::random(8).'.zip';
//        $path = sys_get_temp_dir().'/'.$name;
//        copy($stub, $path);
//        $file = new UploadedFile($path, $name, 'applications/zip', null, true);
//        $user = factory(User::class)->create();
//        $this->be($user);
//        $project = factory(Project::class)->create();
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
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/files/'.$file->id.'/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the files edit page functions for other users.
     */
    public function testFilesEditOtherUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->get('/files/'.$file->id.'/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the files edit page functions for other users.
     */
    public function testFilesEditCollaboratingUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $file->version->project->collaborators()->attach($otherUser);
        $response = $this
            ->actingAs($otherUser)
            ->get('/files/'.$file->id.'/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the files edit page doesn't work for git projects.
     */
    public function testFilesEditGit(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var File $file */
        $file = factory(File::class)->create();
        $file->version->project->git = 'some.uri';
        $file->version->project->save();
        $response = $this
            ->actingAs($user)
            ->get('/files/'.$file->id.'/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdate(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var File $file */
        $file = factory(File::class)->create();
        $data = 'import time
time.localtime()';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/'.$file->id, ['file_content' => $data]);
        $response->assertRedirect('/projects/'.$file->version->project->slug.'/edit')->assertSessionHas('successes');
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertEquals($data, $file->content);
    }

    /**
     * Check the files can't be stored by other users.
     */
    public function testFilesUpdateOtherUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $data = 'import time
time.localtime()';
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/files/'.$file->id, ['file_content' => $data]);
        $response->assertStatus(403);
    }

    /**
     * Check the files can't be stored by other users.
     */
    public function testFilesUpdateCollaboratingUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $file->version->project->collaborators()->attach($otherUser);
        $data = 'import time
time.localtime()';
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/files/'.$file->id, ['file_content' => $data]);
        $response->assertStatus(302);
    }

    /**
     * Check the files can't be updated when project uses git.
     */
    public function testFilesUpdateGit(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var File $file */
        $file = factory(File::class)->create();
        $file->version->project->git = 'some.uri';
        $file->version->project->save();
        $data = 'import time
time.localtime()';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/'.$file->id, ['file_content' => $data]);
        $response->assertStatus(403);
    }

    /**
     * Check the files can be deleted.
     */
    public function testFilesDestroy(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('delete', '/files/'.$file->id);
        $response->assertRedirect('/projects/'.$file->version->project->slug.'/edit')->assertSessionHas('successes');
    }

    /**
     * Check the files can't be deleted from git managed project.
     */
    public function testFilesDestroyGit(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var File $file */
        $file = factory(File::class)->create();
        $file->version->project->git = 'some.uri';
        $file->version->project->save();
        $response = $this
            ->actingAs($user)
            ->call('delete', '/files/'.$file->id);
        $response->assertStatus(403);
    }

    /**
     * Check the files create page functions.
     */
    public function testFilesCreate(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/files/create?version='.$version->id);
        $response->assertStatus(200)
            ->assertViewHas('version', Version::find($version->id));
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesStore(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $response = $this
            ->actingAs($user)
            ->post('/files', ['name' => 'test.py', 'file_content' => '# test', 'version_id' => $version->id]);
        $response->assertRedirect('/projects/'.$version->project->slug.'/edit')
            ->assertSessionHas('successes');
        /** @var File $file */
        $file = File::all()->last();
        $this->assertEquals('test.py', $file->name);
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdateLintWarning(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $data = 'import time';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/'.$file->id, ['file_content' => $data]);
        $response->assertRedirect('/files/'.$file->id.'/edit')->assertSessionHas('warnings');
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdateLintError(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $data = 'imprt time';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/'.$file->id, ['file_content' => $data]);
        $response->assertRedirect('/files/'.$file->id.'/edit')->assertSessionHasErrors();
    }

    /**
     * Check the files can be viewed (publicly).
     */
    public function testFilesView(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $response = $this
            ->call('get', '/files/'.$file->id);
        $response->assertStatus(200)->assertViewHas(['file']);
    }
}
