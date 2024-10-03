<?php

namespace CapsulesCodes\Population\Tests\Cases;

use Orchestra\Testbench\TestCase as BaseTestCase;
use CapsulesCodes\Population\Tests\App\Traits\Configurable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Env;
use CapsulesCodes\Population\Providers\PopulationServiceProvider;


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
            'database' => Env::get( 'SQLITE_DATABASE_ONE', 'tests/App/Database/laravel-population-sqlite-one.sqlite' ),
            'prefix' => '',
            'foreign_key_constraints' => true,

        ] );

        $app[ 'config' ]->set( 'database.connections.two', [

            'driver' => 'sqlite',
            'database' => Env::get( 'SQLITE_DATABASE_TWO', 'tests/App/Database/laravel-population-sqlite-two.sqlite' ),
            'prefix' => '',
            'foreign_key_constraints' => true,

        ] );
    }

    protected function getPackageProviders( $app ) : array
    {
        return [ PopulationServiceProvider::class ];
    }
}
