<?php

declare(strict_types=1);

Route::get('get/{project}/json', 'PublicController@projectJson')->name('project.json');
Route::get('list/json', 'PublicController@listJson')->name('list.json');
Route::get('search/{words}/json', 'PublicController@searchJson')->name('search.json');
Route::get('categories/json', 'PublicController@categoriesJson')->name('categories.json');
Route::get('category/{category}/json', 'PublicController@categoryJson')->name('category.json');

Route::get('files/{project}/json', 'MchController@filesJson')->name('category.json');
