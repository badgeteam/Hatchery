<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.User.*', static function (User $user) {
    /** @var User $authUser */
    $authUser = Auth::user();

    return Auth::check() && $user->id === $authUser->id;
});
