<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FileTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    public function testUploadFile()
    {
        $stub = __DIR__.'/heart.png';
        $name = str_random(8).'.png';
        $path = sys_get_temp_dir().'/'.$name;
        copy($stub, $path);
        $file = new UploadedFile($path, $name, filesize($path), 'image/png', null, true);
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();

        $response = $this
            ->actingAs($user)
            ->post('/upload/'.$project->versions->last()->id, ['file' => $file]);
        $response->assertStatus(200);

        $this->assertCount(2, File::all()); // you get a free __init__.py
        $this->assertEquals('__init__.py', File::first()->name);
        $this->assertEquals($name, File::where('name', '!=', '__init__.py')->first()->name);
    }

//
//    public function testUploadIllegalFile()
//    {
//        $stub = __DIR__.'/empty.zip';
//        $name = str_random(8).'.zip';
//        $path = sys_get_temp_dir().'/'.$name;
//        copy($stub, $path);
//        $file = new UploadedFile($path, $name, filesize($path), 'applications/zip', null, true);
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
    public function testFilesEdit()
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
    public function testFilesEditOtherUser()
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
     * Check the files can be stored.
     */
    public function testFilesUpdate()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $data = 'import time
time.localtime()';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/'.$file->id, ['file_content' => $data]);
        $response->assertRedirect('/projects/'.$file->version->project->slug.'/edit')->assertSessionHas('successes');
        $this->assertEquals($data, File::find($file->id)->content);
    }

    /**
     * Check the files can't be stored by other users.
     */
    public function testFilesUpdateOtherUser()
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
     * Check the files can be deleted.
     */
    public function testFilesDestroy()
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
     * Check the files create page functions.
     */
    public function testFilesCreate()
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
    public function testFilesStore()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $response = $this
            ->actingAs($user)
            ->post('/files', ['name' => 'test.py', 'file_content' => '# test', 'version_id' => $version->id]);
        $response->assertRedirect('/projects/'.$version->project->slug.'/edit')
            ->assertSessionHas('successes');

        $file = File::all()->last();
        $this->assertEquals('test.py', $file->name);
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdateLintWarning()
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
    public function testFilesUpdateLintError()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $data = 'imprt time';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/'.$file->id, ['file_content' => $data]);
        $response->assertRedirect('/files/'.$file->id.'/edit')->assertSessionHas('errors');
    }

    /**
     * Check the files can be viewed (publicly).
     */
    public function testFilesView()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $response = $this
            ->call('get', '/files/'.$file->id);
        $response->assertStatus(200)->assertViewHas(['file']);
    }
}
