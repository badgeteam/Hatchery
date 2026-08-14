<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

use App\Http\Controllers\MchController;
use Illuminate\Support\Facades\Route;

Route::get('devices', [MchController::class, 'devices']);
Route::get('{device}/types', [MchController::class, 'types']);
Route::get('{device}/{type}/categories', [MchController::class, 'categories']);
Route::get('{device}/{type}/{category}', [MchController::class, 'apps']);
Route::get('{device}/{type}/{category}/{app}', [MchController::class, 'app']);

//Route::get('{device}/{type}/{category}/{app}/zip', [MchController::class, 'zip']);
//Route::get('{device}/{type}/{category}/{app}/icon', [MchController::class, 'icon']);

Route::get('{device}/{type}/{category}/{app}/{file}', [MchController::class, 'file'])->name('mch.file');
