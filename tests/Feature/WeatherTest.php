<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WeatherTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * This is a terrible test since it relies on external data
     * from https://api.darksky.net/
     */
    public function testWeather()
    {
        $response = $this->json('GET', '/weather');
        $response->assertStatus(200)->assertJsonStructure([
            'hourly' => [
                'data' => [
                    0 => ['temperature', 'icon'],
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    10,
                    11,
                    12,
                    13,
                    14,
                    15,
                    16,
                    17,
                    18,
                    19,
                    20,
                    21,
                    22,
                    23,
                    24,
                    25,
                    26,
                    27,
                    28,
                    29,
                    30,
                    31,
                    32,
                    33,
                    34,
                    35,
                    36,
                    37,
                    38,
                    39,
                    40,
                    41,
                    42,
                    43,
                    44,
                    45,
                    46,
                    47,
                    48,
                ]
            ]
        ]);
    }

    /**
     * Should be cached now ;)
     */
    public function testWeatherCached()
    {
        $response = $this->json('GET', '/weather');
        $response->assertStatus(200);
        $this->assertTrue(Cache::has('weather'));
        $data = json_decode(Cache::get('weather'));
        $response = $this->json('GET', '/weather');
        $response->assertStatus(200)->assertJson(['hourly' => ['data' => [0 => ['time' => $data->hourly->data[0]->time]]]]);
    }

}
