<?php

namespace CapsulesCodes\Population\Tests\Cases;

use CapsulesCodes\Population\Providers\PopulationServiceProvider;
use CapsulesCodes\Population\Tests\App\Traits\Configurable;
use Illuminate\Support\Facades\Process;
use Orchestra\Testbench\TestCase as BaseTestCase;


abstract class SQLiteTestCase extends BaseTestCase
{
    use Configurable;


    protected function initialize() : void
    {
        Process::run( "touch tests/App/Database/laravel-population-sqlite-one.sqlite" );

        Process::run( "touch tests/App/Database/laravel-population-sqlite-two.sqlite" );
    }

    protected function finalize() : void
    {
        Process::run( "rm tests/App/Database/laravel-population-sqlite-one.sqlite" );

        Process::run( "rm tests/App/Database/laravel-population-sqlite-two.sqlite" );
    }

    protected function getEnvironmentSetUp( $app ) : void
    {
        $app[ 'config' ]->set( 'database.default', 'one' );

        $app[ 'config' ]->set( 'database.connections.one', [

            'driver' => 'sqlite',
            'database' => 'tests/App/Database/laravel-population-sqlite-one.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,

        ] );

        $app[ 'config' ]->set( 'database.connections.two', [

            'driver' => 'sqlite',
            'database' => 'tests/App/Database/laravel-population-sqlite-two.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,

        ] );
    }

    protected function getPackageProviders( $app ) : array
    {
        return [ PopulationServiceProvider::class ];
    }
}
