<?php

namespace CapsulesCodes\Population\Tests\App\Database\Seeders;

use Illuminate\Database\Seeder;
use CapsulesCodes\Population\Tests\App\Models\Base\Foo;


class FooSeeder extends Seeder
{
    public function run() : void
    {
        Foo::factory()->count( 1 )->create();
    }
}
