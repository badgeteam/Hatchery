<?php

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
    Route::resource('badges', 'BadgesController', ['except' => ['index', 'show']]);

    Route::resource('files', 'FilesController', ['except' => 'show']);
    Route::any('create-icon', 'FilesController@createIcon')->name('files.create-icon');
    Route::post('lint-content/{file}', 'FilesController@lint')->name('files.lint');
    Route::post('process-file/{file}', 'FilesController@process')->name('files.process');

    Route::get('profile', 'UsersController@redirect');
    Route::resource('users', 'UsersController');
    Route::resource('votes', 'VotesController', ['only' => ['store', 'update', 'destroy']]);
});

Route::resource('projects', 'ProjectsController', ['only' => ['index', 'show']]);
Route::resource('badges', 'BadgesController', ['only' => ['index', 'show']]);
Route::resource('files', 'FilesController', ['only' => 'show']);
Route::get('download/{file}', 'FilesController@download')->name('files.download');
