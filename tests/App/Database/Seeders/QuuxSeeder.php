<?php

namespace CapsulesCodes\Population\Tests\App\Database\Seeders;

use Illuminate\Database\Seeder;
use CapsulesCodes\Population\Tests\App\Models\Base\Quux;


class QuuxSeeder extends Seeder
{
    public function run() : void
    {
        Quux::factory()->count( 1 )->create();
    }
}
