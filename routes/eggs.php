<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('get/{project}/json', [PublicController::class, 'projectJson'])->name('project.json');
Route::get('list/json', [PublicController::class, 'listJson'])->name('list.json');
Route::get('search/{words}/json', [PublicController::class, 'searchJson'])->name('search.json');
Route::get('categories/json', [PublicController::class, 'categoriesJson'])->name('categories.json');
Route::get('category/{category}/json', [PublicController::class, 'categoryJson'])->name('category.json');
