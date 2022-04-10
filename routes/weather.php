<?php

declare(strict_types=1);

Route::get('/', 'WeatherController@show')->name('weather');
Route::get('{location}', 'WeatherController@location')->name('weather.location');
