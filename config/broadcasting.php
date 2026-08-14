<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Broadcast Connections
|--------------------------------------------------------------------------
|
| Laravel dropped the "redis" broadcast connection from its shipped defaults,
| but Hatchery broadcasts over Redis into laravel-echo-server, so it is
| declared here. The reverb, pusher, ably, log and null connections are
| merged in from the framework defaults.
|
*/

return [

    'connections' => [

        'redis' => [
            'driver'     => 'redis',
            'connection' => env('BROADCAST_REDIS_CONNECTION', 'default'),
        ],

    ],

];
