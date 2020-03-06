<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class FilePolicy.
 *
 * @author annejan@badge.team
 */
class FilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create files.
     *
     * @return bool
     */
    public function create(): bool
    {
        // Everybody can create files
        return true;
    }

    /**
     * Determine whether the user can update the file.
     *
     * @param User $user
     * @param File $file
     *
     * @return bool
     */
    public function update(User $user, File $file): bool
    {
        if ($file->version->project->git !== null) {
            return false;   // No manual fucking with git managed project
        }
        // Normal users can only change files in their own project or projects they collaborate on
        return $user->admin || $user->id == $file->version->project->user->id || $file->version->project
                ->collaborators()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the file.
     *
     * @param User $user
     * @param File $file
     *
     * @return bool
     */
    public function delete(User $user, File $file): bool
    {
        if ($file->version->project->git !== null) {
            return false;   // No manual fucking with git managed project
        }
        // Normal users can only delete  files in their own project
        return $user->admin || $user->id == $file->version->project->user->id;
    }
}
