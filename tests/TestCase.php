<?php

namespace CapsulesCodes\Population\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use CapsulesCodes\Population\Providers\PopulationServiceProvider;


abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders( $app )
    {
        return [ PopulationServiceProvider::class ];
    }
}
