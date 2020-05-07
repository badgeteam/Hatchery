<?php

use App\Models\User;

Broadcast::channel('App.User.*', function (User $user) {
    /** @var User $authUser */
    $authUser = Auth::user();

    return Auth::check() && $user->id === $authUser->id;
});
