<?php

namespace CapsulesCodes\Population\Tests\Cases;

use Orchestra\Testbench\TestCase as BaseTestCase;
use CapsulesCodes\Population\Tests\App\Traits\Configurable;
use CapsulesCodes\Population\Providers\PopulationServiceProvider;


abstract class TestCase extends BaseTestCase
{
    use Configurable;

    protected function getPackageProviders( $app ) : array
    {
        return [ PopulationServiceProvider::class ];
    }
}
