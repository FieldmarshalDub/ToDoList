<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Task;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->realText(100),
        'scheduled_date' => $faker->date(),
        'real_date' => $faker->date(),
        'status' => 'in progress',
        'board_id' => 1,
        'user_id' => 1,
    ];
});
