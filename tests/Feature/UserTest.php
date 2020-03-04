<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use ParagonIE\ConstantTime\Base32 as ParagonieBase32;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/**
 * Class UserTest.
 *
 * @author annejan@badge.team
 */
class UserTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * login failed get redirected.
     */
    public function testLoginFail(): void
    {
        $response = $this
            ->withSession(['_token' => 'test'])
            ->post('/login', [
                'email' => 'annejan@noprotocol.nl',
                'password' => 'badPass',
                '_token' => 'test',
            ]);
        $response->assertStatus(302)
            ->assertRedirect('')
            ->assertSessionHas('errors')
            ->assertSessionHas('_old_input', [
                'email' => 'annejan@noprotocol.nl',
                '_token' => 'test',
            ]);
    }

    /**
     * Login, go to home.
     */
    public function testLogin(): void
    {
        $password = $this->faker->password;
        $user = factory(User::class)->create(['password' => bcrypt($password)]);
        $response = $this
            ->withSession(['_token' => 'test'])
            ->post('/login', [
                'email' => $user->email,
                'password' => $password,
                '_token' => 'test',
            ]);
        $response->assertStatus(302)->assertRedirect('/projects');
    }

    /**
     * Check the homepage / dashboard.
     */
    public function testHome(): void
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
    public function testLogout(): void
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
    public function testUserResetPassword(): void
    {
        Notification::fake();

        $user = factory(User::class)->create();

        $response = $this
            ->withSession(['_token' => 'test'])
            ->post('password/email', [
                'email' => $user->email,
                '_token' => 'test',
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

        $response = $this->get('/password/reset/' . $token);
        $response->assertStatus(200);

        $password = $this->faker->password(8, 20);

        $response = $this
            ->withSession(['_token' => 'test'])
            ->post('/password/reset', [
                'email' => $user->email,
                'token' => $token,
                'password' => $password,
                'password_confirmation' => $password,
                '_token' => 'test',
            ]);
        $response->assertStatus(302)->assertRedirect('/projects');

        $response = $this
            ->withSession(['_token' => 'test'])
            ->post('/login', [
                'email' => $user->email,
                'password' => $password,
                '_token' => 'test',
            ]);
        $response->assertStatus(302)->assertRedirect('/home');
    }

    /**
     * Register, go to /home . .
     */
    public function testRegister(): void
    {
        $password = $this->faker->password(8, 20);
        $email = $this->faker->email;
        $response = $this
            ->withSession(['_token' => 'test'])
            ->post('/register', [
                'name' => $this->faker->name,
                'email' => $email,
                'editor' => 'default',
                'password' => $password,
                'password_confirmation' => $password,
                '_token' => 'test',
            ]);
        $response->assertStatus(302)->assertRedirect('/projects');
    }

    /**
     * Check the user can be deleted.
     */
    public function testUserDestroy(): void
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
    public function testUserDestroyOtherUser(): void
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
    public function testUserDestroyAdminUser(): void
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
    public function testUserEdit(): void
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/users/' . $user->id . '/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the user edit page functions.
     */
    public function testUserEditOtherUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->get('/users/' . $user->id . '/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the user can be stored.
     */
    public function testUserUpdate(): void
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/users/' . $user->id, [
                'name' => 'Henk',
                'email' => 'henk@annejan.com',
                'editor' => 'vim',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
    }

    /**
     * Check the other user can't be stored.
     */
    public function testUserUpdateOtherUser(): void
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/users/' . $user->id, [
                'name' => 'Henk',
                'email' => 'henk@annejan.com',
                'editor' => 'vim',
            ]);
        $response->assertStatus(403);
    }

    /**
     * Check if random user can not view Horizon page.
     */
    public function testUserViewHorizon(): void
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->get('/horizon');
        $response->assertStatus(403);
    }

    /**
     * Check if random user can not view Horizon page.
     */
    public function testAdminUserViewHorizon(): void
    {
        $user = factory(User::class)->create();
        $user->admin = true;
        $user->save();
        $response = $this->actingAs($user)->get('/horizon');
        $response->assertStatus(200);
    }

    /**
     * Check the 2fa 'welcome' page.
     */
    public function testUser2FaForm(): void
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/2fa');
        $response->assertStatus(200);
    }

    /**
     * Check the 2fa secret generation.
     */
    public function testUser2FaGenerateSecret(): void
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->post('/generate2faSecret');
        $response->assertRedirect('/2fa')
            ->assertSessionHas('success');
        $this->assertEquals(16, strlen($user->google2fa_secret));
    }

    /**
     * Check the 2fa registration form.
     */
    public function testUser2FaFormEnabled(): void
    {
        $g2fa = new Google2FA();
        $user = factory(User::class)->create([
            'google2fa_secret' => $g2fa->generateSecretKey(),
        ]);
        $response = $this
            ->actingAs($user)
            ->get('/2fa');
        $response->assertStatus(200);
    }

    /**
     * Check the 2fa registration failure.
     */
    public function testUser2FaEnableFail(): void
    {
        $g2fa = new Google2FA();
        $user = factory(User::class)->create([
            'google2fa_secret' => $g2fa->generateSecretKey(),
        ]);
        $response = $this
            ->actingAs($user)
            ->post('/2fa', [
                'verify-code' => '123456'
            ]);
        $response->assertRedirect('/2fa')
            ->assertSessionHas('error');
        /** @var User $user */
        $user = User::find($user->id);
        $this->assertNotTrue($user->google2fa_enabled);
    }

    /**
     * Check the 2fa registration success.
     */
    public function testUser2FaEnableSuccess(): void
    {
        $g2fa = new Google2FA();
        $user = factory(User::class)->create([
            'google2fa_enabled' => false,
            'google2fa_secret' => $g2fa->generateSecretKey(),
        ]);
        $response = $this
            ->actingAs($user)
            ->post('/2fa', [
                'verify-code' => $g2fa->getCurrentOtp($user->google2fa_secret)
            ]);
        $response->assertRedirect('/2fa')
            ->assertSessionHas('success');
        /** @var User $user */
        $user = User::find($user->id);
        $this->assertNotFalse($user->google2fa_enabled);
    }

    /**
     * Check the 2fa disable failure.
     */
    public function testUser2FaDisableFail(): void
    {
        $g2fa = new Google2FA();
        $user = factory(User::class)->create([
            'google2fa_secret' => $g2fa->generateSecretKey(),
            'google2fa_enabled' => true
        ]);
        $response = $this
            ->actingAs($user)
            ->post('/disable2fa', [
                'current-password' => '123456'
            ]);
        $response->assertRedirect('/')
            ->assertSessionHas('error');
        /** @var User $user */
        $user = User::find($user->id);
        $this->assertNotFalse($user->google2fa_enabled);
    }

    /**
     * Check the 2fa disable success.
     */
    public function testUser2FaDisableSuccess(): void
    {
        $g2fa = new Google2FA();
        $user = factory(User::class)->create([
            'google2fa_secret' => $g2fa->generateSecretKey(),
            'google2fa_enabled' => true
        ]);
        $response = $this
            ->actingAs($user)
            ->post('/disable2fa', [
                'current-password' => 'secret'
            ]);
        $response->assertRedirect('/2fa')
            ->assertSessionHas('success');
        /** @var User $user */
        $user = User::find($user->id);
        $this->assertNotTrue($user->google2fa_enabled);
    }

    /**
     * Check the 2fa verify redirection.
     */
    public function testUser2FaVerifyRedirect(): void
    {
        $g2fa = new Google2FA();
        $user = factory(User::class)->create([
            'google2fa_secret' => $g2fa->generateSecretKey(),
            'google2fa_enabled' => true
        ]);
        $response = $this
            ->actingAs($user)
            ->post('/2faVerify');
        $response->assertStatus(400);
    }
}
