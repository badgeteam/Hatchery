<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PublicTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * A basic test example.
     */
    public function testWelcome()
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertViewHas('users', User::count())
            ->assertViewHas('projects', Project::count());
    }

    /**
     * Check redirect to /login when going to the /home page.
     */
    public function testHomeRedirect()
    {
        $response = $this->get('/home');
        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * Check JSON request Unauthenticated . .
     */
    public function testJSONRedirect()
    {
        $response = $this->json('GET', '/home');
        $response->assertStatus(401)
            ->assertExactJson(['error' => 'Unauthenticated.']);
    }

    /**
     * Check JSON egg request . .
     */
    public function testProjectJSON()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $version = factory(Version::class)->create();
        $version->zip = 'some_path.tar.gz';
        $version->save();

        $response = $this->json('GET', '/eggs/'.$version->project->slug.'/json');
        $response->assertStatus(200)
            ->assertExactJson([
                "description" => "",
                "info" => ["version" => "1"],
                "releases" => [
                    "1" => [
                        [
                            "url" => url("some_path.tar.gz")
                        ],
                    ]
                ]
            ]);
    }
}
