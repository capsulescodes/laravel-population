<?php

namespace CapsulesCodes\Population\Tests\App\Database\Factories;

use CapsulesCodes\Population\Tests\App\Models\Base\Foo;
use Illuminate\Database\Eloquent\Factories\Factory;


class FooFactory extends Factory
{
    protected $model = Foo::class;


    public function definition() : array
    {
        return [
            'foo' => fake()->sentence(),
            'baz' => fake()->boolean(),
            'qux' => fake()->numberBetween()
        ];
    }
}
