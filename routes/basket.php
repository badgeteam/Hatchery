<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('{badge}/list/json', [PublicController::class, 'badgeListJson'])->name('basket.list.json');
Route::get('{badge}/search/{words}/json', [PublicController::class, 'badgeSearchJson'])->name('basket.search.json');
Route::get('{badge}/categories/json', [PublicController::class, 'badgeCategoriesJson'])
    ->name('basket.categories.json');
Route::get('{badge}/category/{category}/json', [PublicController::class, 'badgeCategoryJson'])
    ->name('basket.category.json');
