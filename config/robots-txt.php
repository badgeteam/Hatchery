<?php

declare(strict_types=1);

return [
    'environments' => [
        'production' => [
            'paths' => [
                '*' => [
                    'disallow' => [
                        '',
                    ],
                    'allow' => [],
                ],
            ],
            'sitemaps' => [
                'sitemap.xml',
            ],
        ],
    ],
];
