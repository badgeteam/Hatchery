<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace Tests\Unit;

use App\Support\Weather;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Class WeatherTest.
 *
 * @author annejan@badge.team
 */
class WeatherTest extends TestCase
{
    /**
     * A forecast as Open-Meteo returns one, trimmed to two hours.
     *
     * @param array<string, array<int, mixed>> $overrides
     *
     * @return array<string, mixed>
     */
    private function forecast(array $overrides = []): array
    {
        return [
            'latitude'           => 52.222,
            'longitude'          => 5.713,
            'timezone'           => 'Europe/Amsterdam',
            'utc_offset_seconds' => 7200,
            'hourly'             => array_merge([
                'time'                      => [1786665600, 1786669200],
                'temperature_2m'            => [20.8, 19.4],
                'apparent_temperature'      => [18.5, 17.2],
                'dew_point_2m'              => [5.2, 5.0],
                'relative_humidity_2m'      => [36, 41],
                'pressure_msl'              => [1018.7, 1018.2],
                'wind_speed_10m'            => [7.9, 8.4],
                'wind_gusts_10m'            => [13.3, 14.0],
                'wind_direction_10m'        => [119, 122],
                'cloud_cover'               => [0, 55],
                'precipitation'             => [0.0, 0.3],
                'precipitation_probability' => [0, 25],
                'visibility'                => [38680.0, 24140.0],
                'uv_index'                  => [0.0, 1.4],
                'weather_code'              => [0, 3],
                'is_day'                    => [0, 1],
            ], $overrides),
        ];
    }

    /**
     * The query has to carry the split coordinates and the hourly variables.
     */
    public function testUrlBuildsAnOpenMeteoQuery(): void
    {
        $url = Weather::url('52.2222546,5.7222956');

        $this->assertStringStartsWith('forecast?', $url);

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame('52.2222546', $query['latitude']);
        $this->assertSame('5.7222956', $query['longitude']);
        $this->assertSame(Weather::HOURLY, $query['hourly']);
        $this->assertSame('unixtime', $query['timeformat']);
        $this->assertSame('kmh', $query['windspeed_unit']);
        $this->assertSame('auto', $query['timezone']);
    }

    /**
     * Whitespace after the comma is accepted by the route, so it must not end
     * up in the query.
     */
    public function testUrlTrimsTheCoordinates(): void
    {
        parse_str((string) parse_url(Weather::url('52.28, 5.52'), PHP_URL_QUERY), $query);

        $this->assertSame('52.28', $query['latitude']);
        $this->assertSame('5.52', $query['longitude']);
    }

    /**
     * The envelope Dark Sky put around the hourly block.
     */
    public function testTranslateKeepsTheDarkskyEnvelope(): void
    {
        $result = Weather::translate($this->forecast());

        $this->assertSame(52.222, $result['latitude']);
        $this->assertSame(5.713, $result['longitude']);
        $this->assertSame('Europe/Amsterdam', $result['timezone']);
        $this->assertSame(2.0, $result['offset']);
        $this->assertArrayHasKey('hourly', $result);
        $this->assertArrayHasKey('summary', $result['hourly']);
        $this->assertArrayHasKey('icon', $result['hourly']);
        $this->assertCount(2, $result['hourly']['data']);
    }

    /**
     * Every field a badge reads, named as Dark Sky named it.
     */
    public function testTranslateMapsEveryHourlyField(): void
    {
        $hour = Weather::translate($this->forecast())['hourly']['data'][0];

        $this->assertSame(1786665600, $hour['time']);
        $this->assertSame('Clear', $hour['summary']);
        $this->assertSame(20.8, $hour['temperature']);
        $this->assertSame(18.5, $hour['apparentTemperature']);
        $this->assertSame(5.2, $hour['dewPoint']);
        $this->assertSame(1018.7, $hour['pressure']);
        $this->assertSame(7.9, $hour['windSpeed']);
        $this->assertSame(13.3, $hour['windGust']);
        $this->assertSame(119, $hour['windBearing']);
        $this->assertSame(0.0, $hour['precipIntensity']);
        $this->assertSame(0.0, $hour['uvIndex']);
    }

    /**
     * Dark Sky expressed these as 0 to 1, Open-Meteo as percentages.
     */
    public function testTranslateConvertsPercentagesToFractions(): void
    {
        $data = Weather::translate($this->forecast())['hourly']['data'];

        $this->assertSame(0.36, $data[0]['humidity']);
        $this->assertSame(0.0, $data[0]['cloudCover']);
        $this->assertSame(0.0, $data[0]['precipProbability']);

        $this->assertSame(0.41, $data[1]['humidity']);
        $this->assertSame(0.55, $data[1]['cloudCover']);
        $this->assertSame(0.25, $data[1]['precipProbability']);
    }

    /**
     * `units=ca` meant visibility in kilometres, Open-Meteo reports metres.
     */
    public function testTranslateConvertsVisibilityToKilometres(): void
    {
        $data = Weather::translate($this->forecast())['hourly']['data'];

        $this->assertSame(38.68, $data[0]['visibility']);
        $this->assertSame(24.14, $data[1]['visibility']);
    }

    /**
     * Clear and partly cloudy carried a day or night suffix.
     */
    public function testTranslateSuffixesIconsForDayAndNight(): void
    {
        $data = Weather::translate($this->forecast())['hourly']['data'];

        $this->assertSame('clear-night', $data[0]['icon']);
        $this->assertSame('cloudy', $data[1]['icon']);

        $day = Weather::translate($this->forecast([
            'weather_code' => [0, 2],
            'is_day'       => [1, 1],
        ]))['hourly']['data'];

        $this->assertSame('clear-day', $day[0]['icon']);
        $this->assertSame('partly-cloudy-day', $day[1]['icon']);
    }

    /**
     * A spread of WMO codes onto the icon names badges switch artwork on.
     *
     * @param int    $code
     * @param string $icon
     * @param string $summary
     */
    #[DataProvider('weatherCodes')]
    public function testTranslateMapsWeatherCodes(int $code, string $icon, string $summary): void
    {
        $hour = Weather::translate($this->forecast([
            'weather_code' => [$code, $code],
            'is_day'       => [1, 1],
        ]))['hourly']['data'][0];

        $this->assertSame($icon, $hour['icon']);
        $this->assertSame($summary, $hour['summary']);
    }

    /**
     * @return array<string, array{int, string, string}>
     */
    public static function weatherCodes(): array
    {
        return [
            'overcast'     => [3, 'cloudy', 'Overcast'],
            'fog'          => [45, 'fog', 'Fog'],
            'drizzle'      => [53, 'rain', 'Drizzle'],
            'freezing'     => [56, 'sleet', 'Light freezing drizzle'],
            'rain'         => [63, 'rain', 'Rain'],
            'snow'         => [73, 'snow', 'Snow'],
            'showers'      => [81, 'rain', 'Rain showers'],
            'snow showers' => [85, 'snow', 'Light snow showers'],
            'thunder'      => [95, 'thunderstorm', 'Thunderstorm'],
        ];
    }

    /**
     * An unknown code should not blow up, it should look overcast.
     */
    public function testTranslateFallsBackForUnknownCodes(): void
    {
        $hour = Weather::translate($this->forecast([
            'weather_code' => [42, 42],
        ]))['hourly']['data'][0];

        $this->assertSame('cloudy', $hour['icon']);
        $this->assertSame('Unknown', $hour['summary']);
    }

    /**
     * Open-Meteo returns null where a model has no reading, and a missing value
     * is not the same as zero.
     */
    public function testTranslateKeepsMissingReadingsNull(): void
    {
        $hour = Weather::translate($this->forecast([
            'visibility'                => [null, null],
            'precipitation_probability' => [null, null],
            'wind_direction_10m'        => [null, null],
        ]))['hourly']['data'][0];

        $this->assertNull($hour['visibility']);
        $this->assertNull($hour['precipProbability']);
        $this->assertNull($hour['windBearing']);
    }

    /**
     * The period summary is the most frequent condition in the window.
     */
    public function testTranslateSummarisesThePeriodByDominantCondition(): void
    {
        $result = Weather::translate($this->forecast([
            'time'         => [1, 2, 3],
            'weather_code' => [0, 63, 63],
        ]));

        $this->assertSame('Rain', $result['hourly']['summary']);
        $this->assertSame('rain', $result['hourly']['icon']);
    }

    /**
     * A response without an hourly block should translate to an empty forecast
     * rather than throwing.
     */
    public function testTranslateSurvivesAnEmptyResponse(): void
    {
        $result = Weather::translate([]);

        $this->assertSame([], $result['hourly']['data']);
        $this->assertSame('GMT', $result['timezone']);
        $this->assertSame(0.0, $result['offset']);
        $this->assertNull($result['latitude']);
    }
}
