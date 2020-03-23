<?php

use App\Models\User;

Broadcast::channel('App.User.*', function (User $user) {
    /** @var User|null $authUser */
    $authUser = Auth::user();
    return Auth::check() && $user->id === $authUser->id;
});
