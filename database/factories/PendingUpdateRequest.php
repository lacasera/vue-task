<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\PendingUpdateRequest;
use App\User;
use Faker\Generator as Faker;

$factory->define(PendingUpdateRequest::class, function (Faker $faker) {
    return [
        "user_id" =>factory(User::class),
        "data" => json_encode([
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'time_zone' => collect(['CET', 'CST', 'GMT + 1'])->random()
        ])
    ];
});
