<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

/*
|--------------------------------------------------------------------------
| Third Party Services
|--------------------------------------------------------------------------
|
| This file is for storing the credentials for third party services such
| as Postmark, Resend and others. This file provides the de facto
| location for this type of information, allowing packages to have
| a conventional file to locate the various service credentials.
|
| Entries the framework already ships (ses, postmark, resend, slack) are
| merged in automatically, so only the Hatchery specific ones live here.
|
*/

return [

    'weather' => [
        // Open-Meteo needs no key. DARKSKY_LOCATION is honoured so existing
        // deployments keep their location until their .env is updated.
        'location' => env('WEATHER_LOCATION', env('DARKSKY_LOCATION', '52.2822616,5.5218715')),
    ],

];
