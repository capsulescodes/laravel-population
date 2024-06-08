<?php

namespace CapsulesCodes\Population\Tests\App\Database\Seeders;

use CapsulesCodes\Population\Tests\App\Models\Base\Quux;
use Illuminate\Database\Seeder;


class QuuxSeeder extends Seeder
{
    public function run() : void
    {
        Quux::factory()->count( 100 )->create();
    }
}
