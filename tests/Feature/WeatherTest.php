<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace Tests\Feature;

use App\Support\OpenMeteo;
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
     * A forecast as Open-Meteo returns one, trimmed to a single hour.
     *
     * @throws \JsonException
     *
     * @return string
     */
    private function forecast(): string
    {
        return json_encode([
            'latitude'           => 52.222,
            'longitude'          => 5.713,
            'timezone'           => 'Europe/Amsterdam',
            'utc_offset_seconds' => 7200,
            'hourly'             => [
                'time'                      => [1786665600],
                'temperature_2m'            => [20.8],
                'apparent_temperature'      => [18.5],
                'dew_point_2m'              => [5.2],
                'relative_humidity_2m'      => [36],
                'pressure_msl'              => [1018.7],
                'wind_speed_10m'            => [7.9],
                'wind_gusts_10m'            => [13.3],
                'wind_direction_10m'        => [119],
                'cloud_cover'               => [0],
                'precipitation'             => [0.0],
                'precipitation_probability' => [0],
                'visibility'                => [38680.0],
                'uv_index'                  => [0.0],
                'weather_code'              => [0],
                'is_day'                    => [1],
            ],
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Make sure we can fetch weather, and that it arrives in the shape Dark Sky
     * used to return rather than Open-Meteo's own.
     *
     * @throws \JsonException
     */
    public function testWeatherFetching(): void
    {
        $mock = $this->mock(OpenMeteo::class);
        $mock->expects('get')->once()->andReturns($this->forecast());
        $this->app->instance(OpenMeteo::class, $mock);

        $response = $this->json('get', '/weather');
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertSame('Europe/Amsterdam', $json['timezone']);
        $this->assertSame(2, $json['offset']);
        $this->assertSame('Clear', $json['hourly']['summary']);
        $this->assertSame('clear-day', $json['hourly']['data'][0]['icon']);
        $this->assertSame(20.8, $json['hourly']['data'][0]['temperature']);
        $this->assertSame(0.36, $json['hourly']['data'][0]['humidity']);
        $this->assertSame(38.68, $json['hourly']['data'][0]['visibility']);

        // only called once for 2 calls
        $response = $this->json('get', '/weather');
        $response->assertStatus(200);
    }

    /**
     * A cached upstream response should not be fetched again.
     *
     * @throws \JsonException
     */
    public function testWeatherCaching(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->andReturn($this->forecast());
        Cache::shouldReceive('has')
            ->once()
            ->andReturn(true);
        $mock = $this->mock(OpenMeteo::class);
        $mock->expects('get')->never();
        $this->app->instance(OpenMeteo::class, $mock);

        $response = $this->json('get', '/weather');
        $response->assertStatus(200);
        $this->assertSame(20.8, $response->json()['hourly']['data'][0]['temperature']);
    }

    /**
     * Make sure we catch broken fetching of weather.
     */
    public function testWeatherFetching404(): void
    {
        Cache::shouldReceive('has')
            ->once()
            ->andReturn(false);
        $mock = $this->mock(OpenMeteo::class);
        $mock->expects('get')->once()->andReturns('');
        $this->app->instance(OpenMeteo::class, $mock);

        $response = $this->json('get', '/weather');
        $response->assertStatus(404);
    }

    /**
     * Make sure we can fetch weather for a location, and that a location which
     * is not a coordinate pair is rejected before we call anyone.
     *
     * @throws \JsonException
     */
    public function testWeatherLocationFetching(): void
    {
        $mock = $this->mock(OpenMeteo::class);
        $mock->expects('get')->once()->andReturns($this->forecast());
        $this->app->instance(OpenMeteo::class, $mock);

        $response = $this->json('get', '/weather/bla,bla');
        $response->assertStatus(412);

        $response = $this->json('get', '/weather/52.2822616,5.5218715');
        $response->assertStatus(200);
        $this->assertSame('Clear', $response->json()['hourly']['summary']);
    }
}
