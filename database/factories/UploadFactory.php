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
    $key = Str::uuid()->toString();
    $length = $faker->numberBetween(100, 1000);
    // @todo I really hate this.
    $accumulator = 'this-will-magically-be-replaced';
    return compact('key', 'length', 'accumulator');
});
