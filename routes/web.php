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

Route::resource('projects', 'ProjectsController');

Route::post('/upload/{version}', 'FilesController@upload')->name('files.upload');

Route::post('/release/{project}', 'ProjectsController@publish')->name('project.publish');

Route::resource('files', 'FilesController');

Route::resource('users', 'UsersController', ['only' => ['edit', 'update', 'destroy']]);

Route::get('/eggs/get/{project}/json', 'PublicController@projectJson')->name('project.json');

Route::get('/eggs/list/json', 'PublicController@listJson')->name('list.json');
Route::get('/eggs/search/{words}/json', 'PublicController@searchJson')->name('search.json');

Route::get('/eggs/categories/json', 'PublicController@categoriesJson')->name('categories.json');
Route::get('/eggs/category/{category}/json', 'PublicController@categoryJson')->name('category.json');

Route::get('/weather', 'WeatherController@show')->name('weather');
