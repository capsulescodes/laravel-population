<?php

namespace CapsulesCodes\Population\Tests\App\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use CapsulesCodes\Population\Tests\App\Models\Base\Quux;


class QuuxFactory extends Factory
{
    protected $model = Quux::class;

    public function definition() : array
    {
        return [
            'quux' => fake()->sentence(),
            'garply' => fake()->boolean(),
            'waldo' => fake()->numberBetween()
        ];
    }
}
