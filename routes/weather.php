<?php

declare(strict_types=1);

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeatherController::class, 'show'])->name('weather');
Route::get('{location}', [WeatherController::class, 'location'])->name('weather.location');
