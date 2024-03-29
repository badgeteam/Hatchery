<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class BadgesTest.
 *
 * @author annejan@badge.team
 */
class BadgesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Check the badge edit page functions.
     */
    public function testBadgesEdit(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['admin' => true]);
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/badges/' . $badge->slug . '/edit');
        $response->assertStatus(200)
            ->assertViewHas('badge', $badge);
    }

    /**
     * Check the badge edit page functions.
     */
    public function testBadgesEditNonAdmin(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/badges/' . $badge->slug . '/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the badge index page functions.
     */
    public function testBadgesIndex(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        Badge::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/badges');
        $response->assertStatus(200)
            ->assertViewHas('badges', Badge::paginate());
    }

    /**
     * Check the badge create page functions.
     */
    public function testBadgesCreate(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['admin' => true]);
        $this->be($user);
        $response = $this
            ->actingAs($user)
            ->get('/badges/create');
        $response->assertStatus(200);
    }

    /**
     * Check the badge create page functions.
     */
    public function testBadgesCreateNonAdmin(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        $response = $this
            ->actingAs($user)
            ->get('/badges/create');
        $response->assertStatus(403);
    }

    /**
     * Check the badge show page functions.
     */
    public function testBadgesShow(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/badges/' . $badge->slug);
        $response->assertStatus(200)
            ->assertViewHas('badge', $badge);
    }

    /**
     * Check the badge can be stored.
     */
    public function testBadgesStore(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['admin' => true]);
        $this->be($user);
        $response = $this
            ->actingAs($user)
            ->call('post', '/badges', ['name' => $this->faker->name]);
        $response->assertRedirect('/badges')->assertSessionHasNoErrors();
        $this->assertCount(1, Badge::all());
    }

    /**
     * Check the badge can't be stored.
     */
    public function testBadgesStoreNonAdmin(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        $response = $this
            ->actingAs($user)
            ->call('post', '/badges', ['name' => $this->faker->text(1024)]);
        $response->assertStatus(403);
        $this->assertEmpty(Badge::all());
    }

    /**
     * Check the badge can't be stored.
     */
    public function testBadgesStoreNameTooLong(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['admin' => true]);
        $this->be($user);
        $response = $this
            ->actingAs($user)
            ->call('post', '/badges', ['name' => $this->faker->text(1024)]);
        $response->assertRedirect('/badges/create')->assertSessionHasErrors();
        $this->assertEmpty(Badge::all());
    }

    /**
     * Check the badge can be stored.
     */
    public function testBadgesUpdate(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['admin' => true]);
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $name = $this->faker->name;
        $this->assertNotEquals($name, $badge->name);
        $response = $this
            ->actingAs($user)
            ->call('put', '/badges/' . $badge->slug, ['name' => $name]);
        $response->assertRedirect('/badges')->assertSessionHasNoErrors();
        /** @var Badge $badge */
        $badge = Badge::find($badge->id);
        $this->assertEquals($name, $badge->name);
    }

    /**
     * Check the badge can't be stored.
     */
    public function testBadgesUpdateNonAdmin(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $name = $this->faker->name;
        $this->assertNotEquals($name, $badge->name);
        $response = $this
            ->actingAs($user)
            ->call('put', '/badges/' . $badge->slug, ['name' => $name]);
        $response->assertStatus(403);
        $this->assertNotEquals($name, $badge->name);
    }

    /**
     * Check the badge can't be stored.
     */
    public function testBadgesUpdateNameTooLong(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['admin' => true]);
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $name = $this->faker->text(1024);
        $this->assertNotEquals($name, $badge->name);
        $response = $this
            ->actingAs($user)
            ->call('put', '/badges/' . $badge->slug, ['name' => $name]);
        $response->assertRedirect('/badges/' . $badge->slug . '/edit')->assertSessionHasErrors();
        $this->assertNotEquals($name, $badge->name);
    }

    /**
     * Check the badge can be deleted.
     */
    public function testBadgesDelete(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['admin' => true]);
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $this->assertCount(1, Badge::all());
        $response = $this
            ->actingAs($user)
            ->call('delete', '/badges/' . $badge->slug);
        $response->assertRedirect('/badges')->assertSessionHasNoErrors();
        $this->assertEmpty(Badge::all());
    }

    /**
     * Check the badge can't be deleted.
     */
    public function testBadgesDeleteNonAdmin(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = Badge::factory()->create();
        $this->assertCount(1, Badge::all());
        $response = $this
            ->actingAs($user)
            ->call('delete', '/badges/' . $badge->slug);
        $response->assertStatus(403);
        $this->assertCount(1, Badge::all());
    }
}
