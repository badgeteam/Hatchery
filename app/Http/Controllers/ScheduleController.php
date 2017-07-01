<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use stdClass;

class ScheduleController extends Controller
{
    private $minutes = 10;
    private $url = 'https://program.sha2017.org/schedule.json';

    /**
     * Show the application dashboard.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([0,1,2,3,4], 200, // the four days of SHA
            ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Show schedule of a day
     *
     * @param int $day
     * @return JsonResponse
     */
    public function show(int $day): JsonResponse
    {
        $json = $this->getJson();

        $day = $json->schedule->conference->days[$day];

        $data = [];
        $data['version'] = $json->schedule->version;
        $data['date'] = $day->date;
        $data['rooms'] = [];

        foreach ($day->rooms as $name => $items) {
            $events = [];
            foreach ($items as $item) {
                $events[] = [
                    'start' => $item->start,
                    'duration' => $item->duration,
                    'title' => $item->title
                ];
            }
            $data['rooms'][$name] = $events;
        }

        return response()->json($data, 200,
            ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return stdClass
     */
    private function getJson(): stdClass
    {
        if (Cache::has('schedule')) {
            $json = Cache::get('schedule');
        } else {
            $json = file_get_contents($this->url);
            if ($json === false)
            {
                die("Couldn't fetch the file.");
            }
            $expiresAt = Carbon::now()->addMinutes($this->minutes);
            Cache::put('schedule', $json, $expiresAt);
        }

        return json_decode($json);
    }
}
