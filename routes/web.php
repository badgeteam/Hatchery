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
Route::get('/badge/{badge}', 'PublicController@badge')->name('badge');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::resource('projects', 'ProjectsController');

Route::post('/upload/{version}', 'FilesController@upload')->name('files.upload');

Route::post('/release/{project}', 'ProjectsController@publish')->name('project.publish');

Route::resource('files', 'FilesController');
Route::get('create-icon', 'FilesController@createIcon')->name('files.create-icon');

Route::resource('users', 'UsersController', ['only' => ['edit', 'update', 'destroy']]);

Route::get('/eggs/get/{project}/json', 'PublicController@projectJson')->name('project.json');

Route::get('/eggs/list/json', 'PublicController@listJson')->name('list.json');
Route::get('/eggs/search/{words}/json', 'PublicController@searchJson')->name('search.json');

Route::get('/eggs/categories/json', 'PublicController@categoriesJson')->name('categories.json');
Route::get('/eggs/category/{category}/json', 'PublicController@categoryJson')->name('category.json');

Route::get('/basket/{badge}/list/json', 'PublicController@badgeListJson')->name('basket.list.json');
Route::get('/basket/{badge}/search/{words}/json', 'PublicController@badgeSearchJson')->name('basket.search.json');
Route::get('/basket/{badge}/category/{category}/json', 'PublicController@badgeCategoryJson')->name('basket.category.json');

Route::get('/weather', 'WeatherController@show')->name('weather');
