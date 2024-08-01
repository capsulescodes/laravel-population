<?php

namespace CapsulesCodes\Population\Tests\Cases;

use CapsulesCodes\Population\Providers\PopulationServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;


abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders( $app ) : array
    {
        return [ PopulationServiceProvider::class ];
    }
}
