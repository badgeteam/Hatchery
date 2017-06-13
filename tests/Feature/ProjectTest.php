<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Factory;
use Session;

class ProjectTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Check the projects list.
     */
    public function testProjectsIndex()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects');
        $response->assertStatus(200)
            ->assertViewHas('projects', Project::paginate());
    }

    /**
     * Check the projects creation page exists.
     */
    public function testProjectsCreate()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects/create');
        $response->assertStatus(200);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStore()
    {
        $user = factory(User::class)->create();
        $faker = Factory::create();
        $this->assertEmpty(Project::all());
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $faker->name, 'description' => $faker->paragraph]);
        $response->assertRedirect('/projects/'.Project::get()->last()->id.'/edit')->assertSessionHas('successes');
        $this->assertCount(1, Project::all());
    }

    /**
     * Check the projects edit page functions.
     */
    public function testProjectsEdit()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects/'.$project->id.'/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsUpdate()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $faker = Factory::create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/' . $project->id, ['description' => $faker->paragraph]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
    }
}
