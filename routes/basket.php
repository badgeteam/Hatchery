<?php

declare(strict_types=1);

Route::get('{badge}/list/json', 'PublicController@badgeListJson')->name('basket.list.json');
Route::get('{badge}/search/{words}/json', 'PublicController@badgeSearchJson')->name('basket.search.json');
Route::get('{badge}/categories/json', 'PublicController@badgeCategoriesJson')->name('basket.categories.json');
Route::get('{badge}/category/{category}/json', 'PublicController@badgeCategoryJson')->name('basket.category.json');
