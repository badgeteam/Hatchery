<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

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
