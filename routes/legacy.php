<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

use App\Http\Controllers\BadgesController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VotesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'webauthn', '2fa'])->group(function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');

    Route::resource('projects', ProjectsController::class, ['except' => ['index', 'show']]);
    Route::get('import', [ProjectsController::class, 'create'])->name('projects.import');
    Route::post('import-git', [ProjectsController::class, 'import'])->name('projects.import.git');
    Route::get('projects/{project}/rename', [ProjectsController::class, 'renameForm'])->name('projects.rename');
    Route::post('projects/{project}/move', [ProjectsController::class, 'rename'])->name('projects.move');
    Route::get('projects/{project}/pull', [ProjectsController::class, 'pull'])->name('projects.pull');
    Route::post('notify/{project}', [ProjectsController::class, 'notify'])->name('projects.notify');
    Route::post('upload/{version}', [FilesController::class, 'upload'])->name('files.upload');
    Route::post('release/{project}', [ProjectsController::class, 'publish'])->name('project.publish');
    Route::resource('badges', BadgesController::class, ['except' => ['index', 'show']]);

    Route::resource('files', FilesController::class, ['except' => 'show']);
    Route::any('create-icon', [FilesController::class, 'createIcon'])->name('files.create-icon');
    Route::post('lint-content/{file}', [FilesController::class, 'lint'])->name('files.lint');
    Route::post('process-file/{file}', [FilesController::class, 'process'])->name('files.process');

    Route::get('profile', [UsersController::class, 'redirect']);
    Route::resource('users', UsersController::class);
    Route::resource('votes', VotesController::class, ['only' => ['store', 'update', 'destroy']]);
});

Route::resource('projects', ProjectsController::class, ['only' => ['index', 'show']]);
Route::resource('badges', BadgesController::class, ['only' => ['index', 'show']]);
Route::resource('files', FilesController::class, ['only' => 'show']);
Route::get('download/{file}', [FilesController::class, 'download'])->name('files.download');
