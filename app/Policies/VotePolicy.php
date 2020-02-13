<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vote;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class VotePolicy
 * @author annejan@badge.team
 * @package App\Policies
 */
class VotePolicy
{
    use HandlesAuthorization;

    /**
     * Any user can create a Vote.
     *
     * @return bool
     */
    public function create(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update their Vote.
     *
     * @param User $user
     * @param Vote $vote
     *
     * @return bool
     */
    public function update(User $user, Vote $vote): bool
    {
        return $user->admin || $user->id == $vote->user->id;
    }

    /**
     * Determine whether the user can delete the Vote.
     *
     * @param User $user
     * @param Vote $vote
     *
     * @return bool
     */
    public function delete(User $user, Vote $vote): bool
    {
        return $user->admin || $user->id == $vote->user->id;
    }
}
