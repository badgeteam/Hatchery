<?php

namespace App\Policies;

use App\Models\User;
use App\Models\File;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the file.
     *
     * @return mixed
     */
    public function view()
    {
        // Everybody can view files
	return true;
    }

    /**
     * Determine whether the user can create files.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        // Everybody can create files
        return true;
    }

    /**
     * Determine whether the user can update the file.
     *
     * @param  \App\User  $user
     * @param  \App\File  $file
     * @return mixed
     */
    public function update(User $user, File $file)
    {
	// You can only change your own files
	return $user->id == $file->user->id;
    }

    /**
     * Determine whether the user can delete the file.
     *
     * @param  \App\User  $user
     * @param  \App\File  $file
     * @return mixed
     */
    public function delete(User $user, File $file)
    {
	// You can only delete your own files
	return $user->id == $file->user->id;
    }
}
