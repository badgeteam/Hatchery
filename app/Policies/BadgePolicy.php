<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class BadgePolicy.
 *
 * @author annejan@badge.team
 */
class BadgePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can list badges.
     *
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function index()
    {
        // Everybody can list badges
        return true;
    }

    /**
     * Determine whether the user can create badges.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        // Only admin users
        return $user->admin;
    }

    /**
     * Determine whether the user can update the badge.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        // Only admin users
        return $user->admin;
    }

    /**
     * Determine whether the user can delete the badge.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user)
    {
        // Only admin users
        return $user->admin;
    }
}
