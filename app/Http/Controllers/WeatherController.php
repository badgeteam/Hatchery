<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use stdClass;

class WeatherController extends Controller
{
    private $minutes = 10; // max 144 requests/day ;)
    private $url = 'https://api.darksky.net/forecast/66b6f2f983cb00a1781c1d4f3ae71aa2/52.3451,5.4581?units=ca&exclude=currently,alerts,flags,daily,minutely';


    /**
     * Show schedule of a day
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        return response()->json($this->getJson(), 200,
            ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return stdClass
     */
    private function getJson(): stdClass
    {
        if (Cache::has('weather')) {
            $json = Cache::get('weather');
        } else {
            $json = file_get_contents($this->url);
            if ($json === false)
            {
                die("Couldn't fetch the weather from: ". $this->url);
            }
            $expiresAt = Carbon::now()->addMinutes($this->minutes);
            Cache::put('weather', $json, $expiresAt);
        }

        return json_decode($json);
    }
}
