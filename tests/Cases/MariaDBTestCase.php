<?php

namespace CapsulesCodes\Population\Tests\Cases;

use Orchestra\Testbench\TestCase as BaseTestCase;
use CapsulesCodes\Population\Tests\App\Traits\Configurable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Env;
use CapsulesCodes\Population\Providers\PopulationServiceProvider;


abstract class MariaDBTestCase extends BaseTestCase
{
    use Configurable;


    protected function initialize() : void
    {
        Process::run( 'mysql -u root -e "create database laravel_population_mariadb_one"' );

        Process::run( 'mysql -u root -e "create database laravel_population_mariadb_two"' );
    }

    protected function finalize() : void
    {
        Process::run( 'mysql -u root -e "drop database laravel_population_mariadb_one"' );

        Process::run( 'mysql -u root -e "drop database laravel_population_mariadb_two"' );
    }

    protected function getEnvironmentSetUp( $app ) : void
    {
        $app[ 'config' ]->set( 'database.default', 'one' );

        $app[ 'config' ]->set( 'database.connections.one', [

            'driver' => 'mariadb',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => Env::get( 'MARIADB_DATABASE_ONE', 'laravel_population_mariadb_one' ),
            'username' => Env::get( 'MARIADB_USERNAME' ),
            'password' => Env::get( 'MARIADB_PASSWORD' ),
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => [],

        ] );

        $app[ 'config' ]->set( 'database.connections.two', [

            'driver' => 'mariadb',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => Env::get( 'MARIADB_DATABASE_TWO','laravel_population_mariadb_two' ),
            'username' => Env::get( 'MARIADB_USERNAME' ),
            'password' => Env::get( 'MARIADB_PASSWORD' ),
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => [],

        ] );
    }

    protected function getPackageProviders( $app ) : array
    {
        return [ PopulationServiceProvider::class ];
    }
}
