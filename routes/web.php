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
Route::post('search', 'ProjectsController@index')->name('projects.search');
Route::get('search', 'ProjectsController@index')->name('projects.search');

Auth::routes();

Route::middleware(['auth', 'webauthn'])->group(function () {
    Route::get('home', 'HomeController@index')->name('home');

    Route::resource('projects', 'ProjectsController')->except('index');
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
