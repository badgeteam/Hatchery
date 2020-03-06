<?php

namespace App\Support;

use GuzzleHttp\Client;

/**
 * Class Darksky.
 *
 * Since this is just a convenient wrapper for Guzzle, no testing of our own should be needed.
 */
class Darksky
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Darksky constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.darksky.net/forecast/']);
    }

    /**
     * @param string $url
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function get(string $url): string
    {
        $response = $this->client->request('GET', $url);

        return $response->getBody();
    }
}
