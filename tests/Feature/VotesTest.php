<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class VotesTest.
 *
 * @author annejan@badge.team
 */
class VotesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Check that a User can Vote for a Project.
     */
    public function testProjectsVote(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'pig']);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHas('successes');
        $this->assertCount(1, Vote::all());
        /** @var Project $project */
        $project = Project::find($project->id);
        /** @var Vote $vote */
        $vote = $project->votes()->first();
        $this->assertEquals('pig', $vote->type);
    }

    /**
     * Check that a User can Vote for a Project only once.
     */
    public function testProjectsVoteOnce(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'pig']);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHas('successes');
        $this->assertCount(1, Vote::all());
        $response = $this
            ->actingAs($user)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'pig']);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHasErrors();
        $this->assertCount(1, Vote::all());
    }

    /**
     * Check that a Vote has existing type.
     */
    public function testProjectsVoteTypeExists(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'awesome']);
        $response->assertRedirect('')->assertSessionHasErrors();
        $this->assertEmpty(Vote::all());
    }

    /**
     * Check that a user can delete Vote.
     */
    public function testProjectsVoteDelete(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Vote $vote */
        $vote = Vote::factory()->create([
            'project_id' => $project->id,
            'type'       => 'pig',
        ]);
        $response = $this
            ->actingAs($user)
            ->call('delete', '/votes/'.$vote->id);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHas('successes');
        $this->assertEmpty(Vote::all());
    }

    /**
     * Check that a user can delete other persons Vote.
     */
    public function testProjectsVoteDeleteOtherUser(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Vote $vote */
        $vote = Vote::factory()->create([
            'project_id' => $project->id,
            'type'       => 'pig',
        ]);
        $response = $this
            ->actingAs($otherUser)
            ->call('delete', '/votes/'.$vote->id);
        $response->assertStatus(403);
        $this->assertCount(1, Vote::all());
    }

    /**
     * Check that a user can update his/her Vote.
     */
    public function testProjectsVoteUpdates(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Vote $vote */
        $vote = Vote::factory()->create([
            'project_id' => $project->id,
            'type'       => 'pig',
        ]);
        $response = $this
            ->actingAs($user)
            ->call('put', '/votes/'.$vote->id, [
                'type' => 'up',
            ]);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHas('successes');
        /** @var Vote $vote */
        $vote = Vote::find($vote->id);
        $this->assertEquals('up', $vote->type);
    }

    /**
     * Check that a user can't update other persons Vote.
     */
    public function testProjectsVoteUpdatesOther(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Vote $vote */
        $vote = Vote::factory()->create([
            'project_id' => $project->id,
            'type'       => 'pig',
        ]);
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/votes/'.$vote->id, [
                'type' => 'up',
            ]);
        $response->assertStatus(403);
        /** @var Vote $vote */
        $vote = Vote::find($vote->id);
        $this->assertEquals('pig', $vote->type);
    }

    /**
     * Check that Project Score is correct.
     */
    public function testProjectScore(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        $this->assertEquals(0, $project->score);
        $this
            ->actingAs($user)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'pig']);
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals(0, $project->score);
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this
            ->actingAs($otherUser)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'up']);
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals(0.5, $project->score);
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this
            ->actingAs($otherUser)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'down']);
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this
            ->actingAs($otherUser)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'down']);
        /** @var Project $project */
        $project = Project::find($project->id);
        $this->assertEquals(-.25, $project->score);
    }

    /**
     * Check that a User can Vote for a Project.
     */
    public function testProjectsVoteCommentTooLarge(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/votes', [
                'project_id' => $project->id,
                'type'       => 'pig',
                'comment'    => $this->faker->text(1024),
            ]);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHasErrors();
        $this->assertEmpty(Vote::all());
    }

    /**
     * Check that a user can update his/her Vote.
     */
    public function testProjectsVoteUpdateCommentTooLarge(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var Project $project */
        $project = Project::factory()->create();
        /** @var Vote $vote */
        $vote = Vote::factory()->create([
            'project_id' => $project->id,
            'type'       => 'pig',
        ]);

        $response = $this
            ->actingAs($user)
            ->call('put', '/votes/'.$vote->id, [
                'type'    => 'up',
                'comment' => $this->faker->text(1024),
            ]);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHasErrors();
        /** @var Vote $vote */
        $vote = Vote::find($vote->id);
        $this->assertEquals('pig', $vote->type);
    }
}
