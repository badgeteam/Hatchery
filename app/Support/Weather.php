<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Support;

/**
 * Weather.
 *
 * Dark Sky was switched off on 31 March 2023, so the forecast now comes from
 * Open-Meteo. Badges in the field still expect the shape Dark Sky returned, and
 * they cannot be updated, so this translates one into the other rather than
 * exposing a new contract.
 *
 * Units follow what the old endpoint asked for with `units=ca`: degrees Celsius,
 * kilometres per hour, kilometres, hectopascals and millimetres. Fractions that
 * Dark Sky expressed as 0 to 1 arrive from Open-Meteo as percentages.
 *
 * @author annejan@badge.team
 */
class Weather
{
    /** The hourly variables the translation needs. */
    public const HOURLY = 'temperature_2m,apparent_temperature,dew_point_2m,relative_humidity_2m,'
        . 'pressure_msl,wind_speed_10m,wind_gusts_10m,wind_direction_10m,cloud_cover,precipitation,'
        . 'precipitation_probability,visibility,uv_index,weather_code,is_day';

    /**
     * WMO weather codes, as returned in `weather_code`.
     *
     * @var array<int, string>
     */
    private const CONDITIONS = [
        0  => 'Clear',
        1  => 'Mainly clear',
        2  => 'Partly cloudy',
        3  => 'Overcast',
        45 => 'Fog',
        48 => 'Depositing rime fog',
        51 => 'Light drizzle',
        53 => 'Drizzle',
        55 => 'Dense drizzle',
        56 => 'Light freezing drizzle',
        57 => 'Freezing drizzle',
        61 => 'Light rain',
        63 => 'Rain',
        65 => 'Heavy rain',
        66 => 'Light freezing rain',
        67 => 'Freezing rain',
        71 => 'Light snow',
        73 => 'Snow',
        75 => 'Heavy snow',
        77 => 'Snow grains',
        80 => 'Light rain showers',
        81 => 'Rain showers',
        82 => 'Violent rain showers',
        85 => 'Light snow showers',
        86 => 'Snow showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with light hail',
        99 => 'Thunderstorm with hail',
    ];

    /**
     * The Dark Sky icon names, which is what badges switch artwork on.
     *
     * `clear` and `partly-cloudy` gain a `-day` or `-night` suffix.
     *
     * @var array<int, string>
     */
    private const ICONS = [
        0  => 'clear',
        1  => 'clear',
        2  => 'partly-cloudy',
        3  => 'cloudy',
        45 => 'fog',
        48 => 'fog',
        51 => 'rain',
        53 => 'rain',
        55 => 'rain',
        56 => 'sleet',
        57 => 'sleet',
        61 => 'rain',
        63 => 'rain',
        65 => 'rain',
        66 => 'sleet',
        67 => 'sleet',
        71 => 'snow',
        73 => 'snow',
        75 => 'snow',
        77 => 'snow',
        80 => 'rain',
        81 => 'rain',
        82 => 'rain',
        85 => 'snow',
        86 => 'snow',
        95 => 'thunderstorm',
        96 => 'thunderstorm',
        99 => 'thunderstorm',
    ];

    /**
     * Build the Open-Meteo query for a `latitude,longitude` pair.
     *
     * @param string $location
     *
     * @return string
     */
    public static function url(string $location): string
    {
        [$latitude, $longitude] = array_pad(explode(',', $location, 2), 2, '');

        return 'forecast?' . http_build_query([
            'latitude'       => trim($latitude),
            'longitude'      => trim($longitude),
            'hourly'         => self::HOURLY,
            'timeformat'     => 'unixtime',
            'windspeed_unit' => 'kmh',
            'timezone'       => 'auto',
            'forecast_days'  => 2,
        ]);
    }

    /**
     * Translate an Open-Meteo forecast into the shape Dark Sky returned.
     *
     * @param array<string, mixed> $source
     *
     * @return array<string, mixed>
     */
    public static function translate(array $source): array
    {
        /** @var array<string, array<int, mixed>> $hourly */
        $hourly = is_array($source['hourly'] ?? null) ? $source['hourly'] : [];
        /** @var array<int, mixed> $times */
        $times = $hourly['time'] ?? [];

        $data = [];
        foreach (array_keys($times) as $i) {
            $code = (int) self::at($hourly, 'weather_code', $i);

            $data[] = [
                'time'                => (int) self::at($hourly, 'time', $i),
                'summary'             => self::CONDITIONS[$code] ?? 'Unknown',
                'icon'                => self::icon($code, (bool) self::at($hourly, 'is_day', $i)),
                'precipIntensity'     => self::number($hourly, 'precipitation', $i),
                'precipProbability'   => self::fraction($hourly, 'precipitation_probability', $i),
                'temperature'         => self::number($hourly, 'temperature_2m', $i),
                'apparentTemperature' => self::number($hourly, 'apparent_temperature', $i),
                'dewPoint'            => self::number($hourly, 'dew_point_2m', $i),
                'humidity'            => self::fraction($hourly, 'relative_humidity_2m', $i),
                'pressure'            => self::number($hourly, 'pressure_msl', $i),
                'windSpeed'           => self::number($hourly, 'wind_speed_10m', $i),
                'windGust'            => self::number($hourly, 'wind_gusts_10m', $i),
                'windBearing'         => self::integer($hourly, 'wind_direction_10m', $i),
                'cloudCover'          => self::fraction($hourly, 'cloud_cover', $i),
                'uvIndex'             => self::number($hourly, 'uv_index', $i),
                'visibility'          => self::kilometres($hourly, 'visibility', $i),
            ];
        }

        $dominant = self::dominant($hourly['weather_code'] ?? []);

        return [
            'latitude'  => $source['latitude'] ?? null,
            'longitude' => $source['longitude'] ?? null,
            'timezone'  => $source['timezone'] ?? 'GMT',
            'offset'    => (float) (((int) ($source['utc_offset_seconds'] ?? 0)) / 3600),
            'hourly'    => [
                'summary' => self::CONDITIONS[$dominant] ?? 'Unknown',
                'icon'    => self::icon($dominant, true),
                'data'    => $data,
            ],
        ];
    }

    /**
     * The icon for a WMO code, suffixed for day or night where Dark Sky was.
     *
     * @param int  $code
     * @param bool $day
     *
     * @return string
     */
    private static function icon(int $code, bool $day): string
    {
        $icon = self::ICONS[$code] ?? 'cloudy';

        if ($icon === 'clear' || $icon === 'partly-cloudy') {
            return $icon . ($day ? '-day' : '-night');
        }

        return $icon;
    }

    /**
     * The most frequent code in the window, which stands in for Dark Sky's
     * summary of the period as a whole.
     *
     * @param array<int, mixed> $codes
     *
     * @return int
     */
    private static function dominant(array $codes): int
    {
        $counts = array_count_values(array_map('intval', array_filter($codes, 'is_numeric')));

        if ($counts === []) {
            return 0;
        }

        arsort($counts);

        return (int) array_key_first($counts);
    }

    /**
     * @param array<string, array<int, mixed>> $hourly
     * @param string                           $key
     * @param int                              $i
     *
     * @return mixed
     */
    private static function at(array $hourly, string $key, int $i)
    {
        return $hourly[$key][$i] ?? null;
    }

    /**
     * Open-Meteo returns null where a model has no value, and a missing reading
     * is not the same as zero, so it stays null.
     *
     * @param array<string, array<int, mixed>> $hourly
     * @param string                           $key
     * @param int                              $i
     *
     * @return float|null
     */
    private static function number(array $hourly, string $key, int $i): ?float
    {
        $value = self::at($hourly, $key, $i);

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @param array<string, array<int, mixed>> $hourly
     * @param string                           $key
     * @param int                              $i
     *
     * @return int|null
     */
    private static function integer(array $hourly, string $key, int $i): ?int
    {
        $value = self::at($hourly, $key, $i);

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Percentage to the 0 to 1 fraction Dark Sky used.
     *
     * @param array<string, array<int, mixed>> $hourly
     * @param string                           $key
     * @param int                              $i
     *
     * @return float|null
     */
    private static function fraction(array $hourly, string $key, int $i): ?float
    {
        $value = self::number($hourly, $key, $i);

        return $value === null ? null : round($value / 100, 4);
    }

    /**
     * Metres to kilometres.
     *
     * @param array<string, array<int, mixed>> $hourly
     * @param string                           $key
     * @param int                              $i
     *
     * @return float|null
     */
    private static function kilometres(array $hourly, string $key, int $i): ?float
    {
        $value = self::number($hourly, $key, $i);

        return $value === null ? null : round($value / 1000, 2);
    }
}
