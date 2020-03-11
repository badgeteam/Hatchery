<?php

use App\Models\User;

Broadcast::channel('App.User.*', function (User $user) {
    return Auth::check() && $user->id === Auth::user()->id;
});