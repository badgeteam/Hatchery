<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class OpenMeteo.
 *
 * Since this is just a convenient wrapper for Guzzle, no testing of our own should be needed.
 */
class OpenMeteo
{
    /**
     * @var Client
     */
    private $client;

    /**
     * OpenMeteo constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.open-meteo.com/v1/']);
    }

    /**
     * @param string $url
     *
     * @throws GuzzleException
     *
     * @return string
     */
    public function get(string $url): string
    {
        return $this->client->request('GET', $url)->getBody()->getContents();
    }
}
