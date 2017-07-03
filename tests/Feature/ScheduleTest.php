<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ScheduleTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Check the 5 days of SHA
     */
    public function testScheduleDays()
    {
        $response = $this->json('GET', '/schedule/days');
        $response->assertStatus(200)->assertExactJson([0,1,2,3,4]);
    }

    /**
     * This is a terrible test since it relies on external data
     * from https://program.sha2017.org/schedule.json
     */
    public function testScheduleDayOne()
    {
        $response = $this->json('GET', '/schedule/day/1');
        $response->assertStatus(200)->assertJsonStructure([
            'version',
            'date',
            'rooms' => [
                'No' => [
                    0 => [
                        'start',
                        'duration',
                        'title',
                        ]
                ],
                'Pa',
                'Re',
                'Pi',
                'Tau',
                'Explody',
                'Music Lounge',
                'Waag',
                'Tardis room',
                'Belgian Embassy',
                'Italian Embassy',
                'Family Village',
            ]
        ]);
    }

    /**
     * Should be cached now ;)
     */
    public function testScheduleDayOneCached()
    {
        $response = $this->json('GET', '/schedule/day/1');
        $response->assertStatus(200);
        $this->assertTrue(Cache::has('schedule'));
        $data = json_decode(Cache::get('schedule'));
        $response = $this->json('GET', '/schedule/day/1');
        $response->assertStatus(200)->assertJson([
            'version' => $data->schedule->version,
            'date' => '2017-08-05', // day one ;)
        ]);
    }

}
