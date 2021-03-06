<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Theomessin\Tus\Models\Upload;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Upload::class, function (Faker $faker) {
    return [
        'user_id' => 1,
        'key' => Str::uuid()->toString(),
        'length' => $faker->numberBetween(1998, 2019),
        'accumulator' => 'this-will-magically-be-replaced',
    ];
});
