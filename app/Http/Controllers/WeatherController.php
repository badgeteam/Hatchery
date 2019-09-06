<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Class WeatherController
 * @package App\Http\Controllers
 */
class WeatherController extends Controller
{
    private $minutes = 10; // max 144 requests/day ;)
    private $url = '';

    /**
     * Show schedule of a day.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $this->url = 'https://api.darksky.net/forecast/'
            .config('services.darksky.key')
            .'/'.config('services.darksky.location').'?units=ca&exclude=currently,alerts,flags,daily,minutely';

        return response()->json($this->getJson(), 200,
            ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param string $location
     *
     * @return JsonResponse
     */
    public function location(string $location): JsonResponse
    {
        if (preg_match('/^([-+]?)([\d]{1,2})(((\.)(\d+)(,)))(\s*)(([-+]?)([\d]{1,3})((\.)(\d+))?)$/', $location) !== 1) {
            abort(412, 'Location invalid');
        }

        $this->url = 'https://api.darksky.net/forecast/'
            .config('services.darksky.key')
            .'/'.$location.'?units=ca&exclude=currently,alerts,flags,daily,minutely';

        return response()->json($this->getJson(), 200,
            ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
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
            $json = file_get_contents($this->url);
            if ($json === false) {
                abort(404, "Couldn't fetch the weather from: ".$this->url);
            }
            $expiresAt = Carbon::now()->addMinutes($this->minutes);
            Cache::put($key, $json, $expiresAt);
        }

        return json_decode($json);
    }
}
