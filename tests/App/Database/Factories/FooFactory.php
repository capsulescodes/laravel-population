<?php

namespace CapsulesCodes\Population\Tests\App\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use CapsulesCodes\Population\Tests\App\Models\Base\Foo;


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
