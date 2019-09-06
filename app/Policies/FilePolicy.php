<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class FilePolicy
 * @package App\Policies]
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
        // Normal users can only change files in their own project
        return $user->admin || $user->id == $file->version->project->user->id;
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
        // Normal users can only delete  files in their own project
        return $user->admin || $user->id == $file->version->project->user->id;
    }
}
