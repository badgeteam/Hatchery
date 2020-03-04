<?php

namespace App\Support;

use GuzzleHttp\Client;

/**
 * Class Darksky.
 */
class Darksky
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Darksky constructor.
     */
    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.darksky.net/forecast/']);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function get(string $url): string
    {
        $response = $this->client->request('GET', $url);

        return $response->getBody();
    }
}
