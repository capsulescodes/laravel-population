<?php

namespace CapsulesCodes\Population\Tests\App\Database\Seeders;

use CapsulesCodes\Population\Tests\App\Models\Base\Foo;
use Illuminate\Database\Seeder;


class FooSeeder extends Seeder
{
    public function run() : void
    {
        Foo::factory()->count( 100 )->create();
    }
}
