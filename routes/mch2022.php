<?php

declare(strict_types=1);

Route::get('devices', 'MchController@devices');
Route::get('{device}/types', 'MchController@types');
Route::get('{device}/{type}/categories', 'MchController@categories');
Route::get('{device}/{type}/{category}', 'MchController@category');
Route::get('{device}/{type}/{category}/{app}', 'MchController@category');

Route::get('{device}/{type}/{category}/{project}', 'MchController@filesJson');
Route::get('{device}/{type}/{category}/{project}/file/{name}', 'MchController@fileContent');

Route::get('{device}/{type}/{category}/{project}/zip', 'MchController@zip');
Route::get('{device}/{type}/{category}/{project}/icon', 'MchController@icon');
