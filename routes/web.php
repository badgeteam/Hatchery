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
Route::any('search', 'ProjectsController@index')->name('projects.search');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('2fa', 'Auth\TwoFAController@show2faForm')->name('2fa');
    Route::post('generate2faSecret', 'Auth\TwoFAController@generate2faSecret')->name('generate2faSecret');
    Route::post('2fa', 'Auth\TwoFAController@enable2fa')->name('enable2fa');
    Route::post('disable2fa', 'Auth\TwoFAController@disable2fa')->name('disable2fa');
    Route::any('2faVerify', 'Auth\TwoFAController@verify')->name('2faVerify')->middleware('2fa');
});

Route::middleware(['auth', 'webauthn', '2fa'])->group(function () {
    Route::get('home', 'HomeController@index')->name('home');

    Route::resource('projects', 'ProjectsController', ['except' => ['index', 'show']]);
    Route::get('import', 'ProjectsController@create')->name('projects.import');
    Route::post('import-git', 'ProjectsController@import')->name('projects.import.git');
    Route::get('projects/{project}/rename', 'ProjectsController@renameForm')->name('projects.rename');
    Route::post('projects/{project}/move', 'ProjectsController@rename')->name('projects.move');
    Route::get('projects/{project}/pull', 'ProjectsController@pull')->name('projects.pull');
    Route::post('notify/{project}', 'ProjectsController@notify')->name('projects.notify');
    Route::post('upload/{version}', 'FilesController@upload')->name('files.upload');
    Route::post('release/{project}', 'ProjectsController@publish')->name('project.publish');

    Route::resource('files', 'FilesController', ['except' => 'show']);
    Route::any('create-icon', 'FilesController@createIcon')->name('files.create-icon');
    Route::post('lint-content/{file}', 'FilesController@lint')->name('files.lint');
    Route::post('process-file/{file}', 'FilesController@process')->name('files.process');

    Route::get('profile', 'UsersController@redirect');
    Route::resource('users', 'UsersController');
    Route::resource('votes', 'VotesController', ['only' => ['store', 'update', 'destroy']]);
});

Route::resource('projects', 'ProjectsController', ['only' => ['index', 'show']]);
Route::resource('files', 'FilesController', ['only' => 'show']);
Route::get('download/{file}', 'FilesController@download')->name('files.download');
