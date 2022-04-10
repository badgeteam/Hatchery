<?php

declare(strict_types=1);

namespace App\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;

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
     * @return StreamInterface
     * @throws GuzzleException
     */
    public function get(string $url): StreamInterface
    {
        return $this->client->request('GET', $url)->getBody();
    }
}
