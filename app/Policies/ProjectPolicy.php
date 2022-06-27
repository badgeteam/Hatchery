<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class ProjectPolicy.
 *
 * @author annejan@badge.team
 */
class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can list projects.
     *
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function index()
    {
        // Everybody can list projects
        return true;
    }

    /**
     * Determine whether the user can create projects.
     *
     * @return bool
     */
    public function create()
    {
        // Everybody can create projects
        return true;
    }

    /**
     * Determine whether the user can update the project.
     *
     * @param User    $user
     * @param Project $project
     *
     * @return bool
     */
    public function update(User $user, Project $project)
    {
        // Normal users can only change projects they collaborate on
        return ($project->allow_team_fixes && $user->admin) || $user->id === $project->user_id ||
            $project->collaborators()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the project.
     *
     * @param User    $user
     * @param Project $project
     *
     * @return bool
     */
    public function delete(User $user, Project $project)
    {
        // Normal users can only delete their own projects
        return  $user->admin || $user->id === $project->user_id;
    }

    /**
     * Determine whether the user can rename the project.
     *
     * @param User    $user
     * @param Project $project
     *
     * @return bool
     */
    public function rename(User $user, Project $project)
    {
        // Ony admin users can rename projects
        return  $user->admin;
    }

    /**
     * Determine whether the user can `git pull` update the project.
     *
     * @param User    $user
     * @param Project $project
     *
     * @return bool
     */
    public function pull(User $user, Project $project)
    {
        if ($project->git === null) {
            return false;   // No git, no pull
        }
        // Normal users can only pull their own projects or projects they collaborate on
        return  ($project->allow_team_fixes && $user->admin) || $user->id === $project->user_id ||
            $project->collaborators()->where('user_id', $user->id)->exists();
    }
}
