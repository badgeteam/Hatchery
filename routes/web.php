<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'PublicController@index')->name('splash');
Route::get('badge/{badge}', 'PublicController@badge')->name('badge');
Route::any('search', 'ProjectsController@index')->name('projects.search');

Route::get('api', 'PublicController@api');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('2fa', 'Auth\TwoFAController@show2faForm')->name('2fa');
    Route::post('generate2faSecret', 'Auth\TwoFAController@generate2faSecret')->name('generate2faSecret');
    Route::post('2fa', 'Auth\TwoFAController@enable2fa')->name('enable2fa');
    Route::post('disable2fa', 'Auth\TwoFAController@disable2fa')->name('disable2fa');
    Route::any('2faVerify', 'Auth\TwoFAController@verify')->name('2faVerify')->middleware('2fa');
});
