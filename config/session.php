<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

/*
|--------------------------------------------------------------------------
| Session Configuration
|--------------------------------------------------------------------------
|
| Only the options that differ from the framework defaults are listed here;
| everything else is merged in from vendor/laravel/framework/config/session.php.
|
| The "file" driver is kept as the default because this application has no
| sessions table migration. The cookie is scoped to badge.team and marked
| secure, which is why those two options are pinned instead of inherited.
|
*/

return [

    'driver' => env('SESSION_DRIVER', 'file'),

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'domain' => env('SESSION_DOMAIN', 'badge.team'),

    'secure' => env('SESSION_SECURE_COOKIE', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'http_only' => true,

];
