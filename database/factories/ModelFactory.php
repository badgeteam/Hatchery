<?php

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

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

/* @var Factory $factory */
$factory->define(
    App\Models\User::class, function (Faker\Generator $faker) {
        static $password;

        return [
            'name'           => $faker->name,
            'email'          => $faker->unique()->safeEmail,
            'password'       => $password ?: $password = bcrypt('secret'),
            'remember_token' => Str::random(10),
        ];
    }
);

/* @var Factory $factory */
$factory->define(
    App\Models\Project::class, function (Faker\Generator $faker) {
        return [
            'name'        => $faker->name,
            'category_id' => function () {
                return factory(App\Models\Category::class)->create()->id;
            },
        ];
    }
);

/* @var Factory $factory */
$factory->define(
    App\Models\Version::class, function (Faker\Generator $faker) {
        return [
            'revision'   => 1,
            'project_id' => function () {
                return factory(App\Models\Project::class)->create()->id;
            },
        ];
    }
);

/* @var Factory $factory */
$factory->define(
    App\Models\File::class, function (Faker\Generator $faker) {
        return [
            'version_id' => function () {
                return factory(App\Models\Version::class)->create()->id;
            },
            'name'    => $faker->word.'.py',
            'content' => $faker->paragraph,
        ];
    }
);

/* @var Factory $factory */
$factory->define(
    App\Models\Category::class, function (Faker\Generator $faker) {
        return [
            'name' => $faker->name,
        ];
    }
);

/* @var Factory $factory */
$factory->define(
    App\Models\Badge::class, function (Faker\Generator $faker) {
        return [
            'name' => $faker->name,
            'slug' => $faker->slug,
        ];
    }
);

/* @var Factory $factory */
$factory->define(
    App\Models\Vote::class, function (Faker\Generator $faker) {
        return [
            'project_id' => function () {
                return factory(App\Models\Project::class)->create()->id;
            },
        ];
    }
);

/* @var Factory $factory */
$factory->define(
    App\Models\Warning::class, function (Faker\Generator $faker) {
        return [
            'project_id' => function () {
                return factory(App\Models\Project::class)->create()->id;
            },
            'description' => $faker->paragraph,
        ];
    }
);
