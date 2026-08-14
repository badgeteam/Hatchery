<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by bootstrap/app.php within a group which contains
| the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\TwoFAController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'index'])->name('splash');
Route::get('badge/{badge}', [PublicController::class, 'badge'])->name('badge');
Route::any('search', [ProjectsController::class, 'index'])->name('projects.search');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('2fa', [TwoFAController::class, 'show2faForm'])->name('2fa');
    Route::post('generate2faSecret', [TwoFAController::class, 'generate2faSecret'])->name('generate2faSecret');
    Route::post('2fa', [TwoFAController::class, 'enable2fa'])->name('enable2fa');
    Route::post('disable2fa', [TwoFAController::class, 'disable2fa'])->name('disable2fa');
    Route::any('2faVerify', [TwoFAController::class, 'verify'])->name('2faVerify')->middleware('2fa');
});
