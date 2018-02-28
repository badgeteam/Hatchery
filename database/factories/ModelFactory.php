<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name'           => $faker->name,
        'email'          => $faker->unique()->safeEmail,
        'password'       => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Project::class, function (Faker\Generator $faker) {
    return [
        'name'    => $faker->name,
        'user_id' => function () {
            return factory(App\Models\User::class)->create()->id;
        },
    ];
});

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Version::class, function (Faker\Generator $faker) {
    return [
        'revision'   => 1,
        'project_id' => function () {
            return factory(App\Models\Project::class)->create()->id;
        },
    ];
});

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\File::class, function (Faker\Generator $faker) {
    return [
        'version_id' => function () {
            return factory(App\Models\Version::class)->create()->id;
        },
        'name'    => $faker->word.'.py',
        'content' => $faker->paragraph,
    ];
});

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Category::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
    ];
});
