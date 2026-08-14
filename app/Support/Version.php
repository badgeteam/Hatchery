<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Support;

/**
 * Class Version.
 *
 * @author annejan@badge.team
 */
class Version
{
    /**
     * Returns the version of the application by reading public/version.json.
     *
     * @throws \JsonException
     *
     * @return string
     */
    public static function applicationVersion(): string
    {
        // Silence is ok here
        $versionJson = @file_get_contents(public_path('/version.json'));
        if (!$versionJson) {
            return 'Undefined';
        }

        $versionData = json_decode($versionJson, true, 512, JSON_THROW_ON_ERROR);
        if (is_array($versionData) && array_key_exists('version', $versionData)) {
            return $versionData['version'];
        }

        return 'Unknown';
    }
}
