<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class ProjectPolicy.
 */
class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can list projects.
     *
     * @return mixed
     */
    public function index()
    {
        // Everybody can list projects
        return true;
    }

    /**
     * Determine whether the user can create projects.
     *
     * @return mixed
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
     * @return mixed
     */
    public function update(User $user, Project $project)
    {
        // Normal users can only change their own projects
        return $user->admin || $user->id == $project->user->id;
    }

    /**
     * Determine whether the user can delete the project.
     *
     * @param User    $user
     * @param Project $project
     *
     * @return mixed
     */
    public function delete(User $user, Project $project)
    {
        // Normal users can only delete their own projects
        return  $user->admin || $user->id == $project->user->id;
    }
}
