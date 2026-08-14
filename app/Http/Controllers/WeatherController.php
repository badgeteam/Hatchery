<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Http\Controllers;

use App\Support\OpenMeteo;
use App\Support\Weather;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

/**
 * Class WeatherController.
 *
 * The forecast came from Dark Sky until Apple switched that API off on
 * 31 March 2023, after which this endpoint returned a 500 to anything that
 * called it. It now comes from Open-Meteo, translated back into the shape
 * Dark Sky returned, because badges in the field cannot be updated.
 *
 * @author annejan@badge.team
 */
class WeatherController extends Controller
{
    /**
     * @var OpenMeteo
     */
    private $client;

    /**
     * @var int
     */
    private $minutes = 10; // max 144 requests/day per location ;)

    /**
     * @var string
     */
    private $url = '';

    /**
     * WeatherController constructor.
     *
     * @param OpenMeteo $openMeteo
     */
    public function __construct(OpenMeteo $openMeteo)
    {
        $this->client = $openMeteo;
    }

    /**
     * Show weather forecast for today.
     *
     * @throws GuzzleException
     *
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/weather',
        tags: ['External'],
        responses: [
            new OA\Response(response: 'default', ref: '#/components/responses/undocumented'),
        ],
    )]
    public function show(): JsonResponse
    {
        $this->url = Weather::url((string) config('services.weather.location'));

        return response()->json(
            $this->getJson(),
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Show weather forecast for a given location for today.
     *
     * @param string $location
     *
     * @throws GuzzleException
     *
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/weather/{location}',
        tags: ['External'],
        parameters: [
            new OA\Parameter(
                name: 'location',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'geolocation',
                    example: '52.2822616,5.5218715',
                ),
            ),
        ],
        responses: [
            new OA\Response(response: 'default', ref: '#/components/responses/undocumented'),
        ],
    )]
    public function location(string $location): JsonResponse
    {
        if (
            preg_match('/^([-+]?)([\d]{1,2})(((\.)(\d+)(,)))(\s*)(([-+]?)([\d]{1,3})((\.)(\d+))?)$/', $location)
            !== 1
        ) {
            abort(412, 'Location invalid');
        }

        $this->url = Weather::url($location);

        return response()->json(
            $this->getJson(),
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * The upstream response is cached rather than the translation, so a change
     * to the mapping takes effect without waiting for the cache to expire.
     *
     * @throws GuzzleException
     * @throws \JsonException
     *
     * @return array<string, mixed>
     */
    private function getJson(): array
    {
        $key = hash('sha256', $this->url);
        if (Cache::has($key)) {
            $json = Cache::get($key);
        } else {
            $json = $this->client->get($this->url);
            if ($json === '') {
                abort(404, "Couldn't fetch the weather from: " . $this->url);
            }
            $expiresAt = Carbon::now()->addMinutes($this->minutes);
            Cache::put($key, $json, $expiresAt);
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $json, true, 512, JSON_THROW_ON_ERROR);

        return Weather::translate($decoded);
    }
}
