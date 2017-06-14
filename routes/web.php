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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::resource('projects', 'ProjectsController', ['except' => ['show']]);

Route::post('/upload/{version}', 'FilesController@upload')->name('files.upload');

Route::post('/release/{project}', 'ProjectsController@publish')->name('project.publish');

Route::resource('files', 'FilesController', ['except' => ['show']]);

Route::get('/eggs/{slug}/json', 'PublicController@json')->name('project.json')
    ->where(['slug' => '[A-Za-z_\-.0-9]+']);;
