<?php

declare(strict_types=1);

Route::get('devices', 'MchController@devices');
Route::get('{device}/types', 'MchController@types');
Route::get('{device}/{type}/categories', 'MchController@categories');
Route::get('{device}/{type}/{category}', 'MchController@apps');
Route::get('{device}/{type}/{category}/{app}', 'MchController@app');

//Route::get('{device}/{type}/{category}/{app}/zip', 'MchController@zip');
//Route::get('{device}/{type}/{category}/{app}/icon', 'MchController@icon');

Route::get('{device}/{type}/{category}/{app}/{file}', 'MchController@file')->name('mch.file');
