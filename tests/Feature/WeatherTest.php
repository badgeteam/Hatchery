<?php

namespace Tests\Feature;

use App\Support\Darksky;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Class WeatherTest.
 *
 * @author annejan@badge.team
 */
class WeatherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Make sure we can fetch weather.
     */
    public function testWeatherFetching(): void
    {
        $data = new \stdClass();
        $data->test = 'data';
        $mock = $this->mock(Darksky::class);
        $mock->expects('get')->once()->andReturns(json_encode($data));
        $this->app->instance(Darksky::class, $mock);
        $response = $this->get('/weather');
        $response->assertStatus(200);
        $this->assertEquals('{"test":"data"}', $response->getContent());
        // only called once for 2 calls
        $response = $this->get('/weather');
        $response->assertStatus(200);
    }

    /**
     * Make sure we can fetch weather.
     */
    public function testWeatherCaching(): void
    {
        $cacheData = new \stdClass();
        $cacheData->cache = 'test';
        Cache::shouldReceive('get')
            ->once()
            ->andReturn(json_encode($cacheData));
        Cache::shouldReceive('has')
            ->once()
            ->andReturn(true);
        $mock = $this->mock(Darksky::class);
        $mock->expects('get')->never();
        $this->app->instance(Darksky::class, $mock);
        $response = $this->get('/weather');
        $response->assertStatus(200);
        $this->assertEquals('{"cache":"test"}', $response->getContent());
    }

    /**
     * Make sure we catch broken fetching of weather.
     */
    public function testWeatherFetching404(): void
    {
        $data = new \stdClass();
        $data->test = 'data';
        $mock = $this->mock(Darksky::class);
        $mock->expects('get')->once()->andReturns(false);
        $this->app->instance(Darksky::class, $mock);
        $response = $this->get('/weather');
        $response->assertStatus(404);
    }

    /**
     * Make sure we can fetch weather.
     */
    public function testWeatherLocationFetching(): void
    {
        $data = new \stdClass();
        $data->test = 'data';
        $mock = $this->mock(Darksky::class);
        $mock->expects('get')->once()->andReturns(json_encode($data));
        $this->app->instance(Darksky::class, $mock);
        $response = $this->get('/weather/bla,bla');
        $response->assertStatus(412);
        $response = $this->get('/weather/52.2822616,5.5218715');
        $response->assertStatus(200);
        $this->assertEquals('{"test":"data"}', $response->getContent());
    }
}
