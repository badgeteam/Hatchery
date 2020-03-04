<?php

namespace App\Http\Controllers;

use App\Support\Darksky;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Annotations as OA;
use stdClass;

/**
 * Class WeatherController.
 *
 * @author annejan@badge.team
 */
class WeatherController extends Controller
{
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
     * @param Darksky $darksky
     */
    public function __construct(Darksky $darksky)
    {
        $this->client = $darksky;
    }

    /**
     * Show weather forecast for today.
     *
     * @OA\Get(
     *   path="/weather",
     *   tags={"External"},
     *   @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $this->url = config('services.darksky.key')
            .'/'.config('services.darksky.location').'?units=ca&exclude=currently,alerts,flags,daily,minutely';

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
     * @OA\Get(
     *   path="/weather/{location}",
     *   @OA\Parameter(
     *     name="location",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       format="geolocation",
     *       example="52.2822616,5.5218715"
     *     )
     *   ),
     *   tags={"External"},
     *   @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $location
     *
     * @return JsonResponse
     */
    public function location(string $location): JsonResponse
    {
        if (preg_match('/^([-+]?)([\d]{1,2})(((\.)(\d+)(,)))(\s*)(([-+]?)([\d]{1,3})((\.)(\d+))?)$/', $location) !== 1) {
            abort(412, 'Location invalid');
        }

        $this->url = config('services.darksky.key')
            .'/'.$location.'?units=ca&exclude=currently,alerts,flags,daily,minutely';

        return response()->json(
            $this->getJson(),
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @return stdClass
     */
    private function getJson(): stdClass
    {
        $key = hash('sha256', $this->url);
        if (Cache::has($key)) {
            $json = Cache::get($key);
        } else {
            $json = $this->client->get($this->url);
            if ($json === '') {
                abort(404, "Couldn't fetch the weather from: ".$this->url);
            }
            $expiresAt = Carbon::now()->addMinutes($this->minutes);
            Cache::put($key, $json, $expiresAt);
        }

        return json_decode($json);
    }
}
