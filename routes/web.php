<?php

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
Route::get('projects', 'ProjectsController@index')->name('projects.index');
Route::get('projects/{project}', 'ProjectsController@show')->name('projects.show');
Route::post('search', 'ProjectsController@index')->name('projects.search');
Route::get('search', 'ProjectsController@index')->name('projects.search');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('2fa', 'Auth\TwoFAController@show2faForm')->name('2fa');
    Route::post('generate2faSecret', 'Auth\TwoFAController@generate2faSecret')->name('generate2faSecret');
    Route::post('2fa', 'Auth\TwoFAController@enable2fa')->name('enable2fa');
    Route::post('disable2fa', 'Auth\TwoFAController@disable2fa')->name('disable2fa');
    Route::post('2faVerify', 'Auth\TwoFAController@verify')->name('2faVerify')->middleware('2fa');
});

Route::middleware(['auth', 'webauthn', '2fa'])->group(function () {
    Route::get('home', 'HomeController@index')->name('home');

    Route::resource('projects', 'ProjectsController')->except(['index', 'show']);
    Route::get('projects/{project}/rename', 'ProjectsController@renameForm')->name('projects.rename');
    Route::post('projects/{project}/rename', 'ProjectsController@rename')->name('projects.rename');
    Route::post('notify/{project}', 'ProjectsController@notify')->name('projects.notify');
    Route::post('upload/{version}', 'FilesController@upload')->name('files.upload');
    Route::post('release/{project}', 'ProjectsController@publish')->name('project.publish');

    Route::resource('files', 'FilesController');
    Route::get('create-icon', 'FilesController@createIcon')->name('files.create-icon');
    Route::get('download/{file}', 'FilesController@download')->name('files.download');

    Route::get('profile', 'UsersController@redirect');
    Route::resource('users', 'UsersController', ['only' => ['edit', 'update', 'destroy']]);

    Route::resource('votes', 'VotesController', ['only' => ['store', 'destroy']]);
});
