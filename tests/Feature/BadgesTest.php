<?php

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
        $user = factory(User::class)->create(['admin' => true]);
        $this->be($user);
        /** @var Badge $badge */
        $badge = factory(Badge::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/badges/'.$badge->slug.'/edit');
        $response->assertStatus(200)
            ->assertViewHas('badge', $badge);
    }

    /**
     * Check the badge edit page functions.
     */
    public function testBadgesEditNonAdmin(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var Badge $badge */
        $badge = factory(Badge::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/badges/'.$badge->slug.'/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the badge index page functions.
     */
    public function testBadgesIndex(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        factory(Badge::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/badges');
        $response->assertStatus(200)
            ->assertViewHas('badges', Badge::paginate());
    }
}
