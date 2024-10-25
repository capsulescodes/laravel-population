<?php

namespace CapsulesCodes\Population\Tests\Cases;

use CapsulesCodes\Population\Providers\PopulationServiceProvider;
use CapsulesCodes\Population\Tests\App\Traits\Configurable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Env;
use Orchestra\Testbench\TestCase as BaseTestCase;


abstract class PostgreSQLTestCase extends BaseTestCase
{
    use Configurable;


    protected function initialize() : void
    {
        Process::run( 'psql -c "create database laravel_population_pgsql_one;"' );

        Process::run( 'psql -c "create database laravel_population_pgsql_two;"' );
    }

    protected function finalize() : void
    {
        Process::run( 'psql -c "drop database laravel_population_pgsql_one;"' );

        Process::run( 'psql -c "drop database laravel_population_pgsql_two;"' );
    }

    protected function getEnvironmentSetUp( $app ) : void
    {
        $app[ 'config' ]->set( 'database.default', 'one' );

        $app[ 'config' ]->set( 'database.connections.one', [

            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => Env::get( 'POSTGRESQL_DATABASE_ONE', 'laravel_population_pgsql_one' ),
            'username' => Env::get( 'POSTGRESQL_USERNAME' ),
            'password' => Env::get( 'POSTGRESQL_PASSWORD' ),
            'unix_socket' => '',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer'
        ] );

        $app[ 'config' ]->set( 'database.connections.two', [

            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => Env::get( 'POSTGRESQL_DATABASE_TWO', 'laravel_population_pgsql_two' ),
            'username' => Env::get( 'POSTGRESQL_USERNAME' ),
            'password' => Env::get( 'POSTGRESQL_PASSWORD' ),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer'
        ] );
    }

    protected function getPackageProviders( $app ) : array
    {
        return [ PopulationServiceProvider::class ];
    }
}
