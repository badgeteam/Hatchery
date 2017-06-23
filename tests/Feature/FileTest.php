<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Project;
use App\Models\User;
use Faker\Factory;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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

        $this->assertCount(1, File::all());
        $this->assertEquals($name, File::first()->name);
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
     * Check the files can be stored.
     */
    public function testFilesUpdate()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $faker = Factory::create();
        $data = $faker->paragraph;
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertRedirect('/projects/'.$file->version->project->id.'/edit')->assertSessionHas('successes');
        $this->assertEquals($data, File::find($file->id)->content);
    }

}