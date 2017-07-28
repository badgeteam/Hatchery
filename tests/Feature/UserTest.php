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

class UserTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * login failed get redirected.
     */
    public function testLoginFail()
    {
        $response = $this
            ->withSession(['_token'=>'test'])
            ->post('/login', [
                'email' => 'annejan@noprotocol.nl',
                'password' => 'badPass',
                '_token' => 'test'
            ]);
        $response->assertStatus(302)
            ->assertRedirect('')
            ->assertSessionHas('errors')
            ->assertSessionHas('_old_input', [
                "email" => 'annejan@noprotocol.nl',
                "remember" => null
            ]);
    }

    /**
     * Login, go to home
     */
    public function testLogin()
    {
        $faker = Factory::create();
        $password = $faker->password;
        $user = factory(User::class)->create(['password' => bcrypt($password)]);
        $response = $this
            ->withSession(['_token'=>'test'])
            ->post('/login', [
                'email' => $user->email,
                'password' => $password,
                '_token' => 'test'
            ]);
        $response->assertStatus(302)->assertRedirect('/projects');
    }

    /**
     * Check the homepage / dashboard.
     */
    public function testHome()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/home');
        $response->assertStatus(200)
            ->assertViewHas('projects', Project::count())
            ->assertViewHas('users', User::count());
    }

    /**
     * Check the logout functionality.
     */
    public function testLogout()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->post('/logout');
        $response->assertStatus(302)
            ->assertRedirect('');
    }

    /**
     * Test a password change and login (full flow).
     */
    public function testUserResetPassword()
    {
        Notification::fake();

        $user = factory(User::class)->create();

        $response = $this
            ->withSession(['_token'=>'test'])
            ->post('password/email', [
                'email' => $user->email,
                '_token' => 'test'
            ]);
        $response->assertStatus(302)
            ->assertRedirect('/');

        $token = null;

        Notification::assertSentTo(
            [$user],
            ResetPassword::class,
            function ($notification, $channels) use (&$token) {
                $token = $notification->token;
                return true;
            }
        );

        $this->assertNotNull($token);

        $response = $this->get('/password/reset/'.$token);
        $response->assertStatus(200);

        $faker = Factory::create();
        $password = $faker->password;

        $response = $this
            ->withSession(['_token'=>'test'])
            ->post('/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => $password,
            'password_confirmation' => $password,
            '_token' => 'test'
        ]);
        $response->assertStatus(302)->assertRedirect('/projects');

        $response = $this
            ->withSession(['_token'=>'test'])
            ->post('/login', [
                'email' => $user->email,
                'password' => $password,
                '_token' => 'test'
            ]);
        $response->assertStatus(302)->assertRedirect('/home');
    }

    /**
     * Register, go to /home . .
     */
    public function testRegister()
    {
        $faker = Factory::create();
        $password = $faker->password;
        $email = $faker->email;
        $response = $this
            ->withSession(['_token'=>'test'])
            ->post('/register', [
                'name' => $faker->name,
                'email' => $email,
                'editor' => 'default',
                'password' => $password,
                'password_confirmation' => $password,
                '_token' => 'test'
            ]);
        $response->assertStatus(302)->assertRedirect('/projects');
    }

    /**
     * Check the user can be deleted.
     */
    public function testUserDestroy()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('delete', '/users/' . $user->id);
        $response->assertRedirect('/')->assertSessionHas('successes');
        $user = User::withTrashed()->find($user->id);
        $this->assertNotNull($user->deleted_at);
    }

    /**
     * Check the users can't be deleted by other users.
     */
    public function testUserDestroyOtherUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->call('delete', '/users/' . $user->id);
        $response->assertStatus(403);
    }

    /**
     * Check the users can be deleted by admin users.
     */
    public function testUserDestroyAdminUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $otherUser->admin = true;
        $otherUser->save();
        $response = $this
            ->actingAs($otherUser)
            ->call('delete', '/users/' . $user->id);
        $response->assertRedirect('/')->assertSessionHas('successes');
    }

    /**
     * Check the user edit page functions.
     */
    public function testUserEdit()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/users/'.$user->id.'/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the user edit page functions.
     */
    public function testUserEditOtherUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->get('/users/'.$user->id.'/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the user can be stored.
     */
    public function testUserUpdate()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/users/' . $user->id, [
                'name' => 'Henk',
                'email' => 'henk@annejan.com',
                'editor' => 'vim'
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
    }

    /**
     * Check the other user can't be stored.
     */
    public function testUserUpdateOtherUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/users/' . $user->id, [
                'name' => 'Henk',
                'email' => 'henk@annejan.com',
                'editor' => 'vim'
            ]);
        $response->assertStatus(403);
    }


}
