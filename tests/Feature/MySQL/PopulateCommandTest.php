<?php

use Illuminate\Support\Facades\Config;
use CapsulesCodes\Population\Tests\App\Database\Seeders\FooSeeder;
use CapsulesCodes\Population\Tests\App\Database\Seeders\QuuxSeeder;


beforeEach( function()
{
    $this->disk = Storage::build( [ 'driver' => 'local', 'root' => storage_path() ] );

    $this->path = Config::get( 'population.path' );

    $this->parameters = [ '--realpath' => true, '--path' => 'tests/app/database/migrations/one-database/new' ];
} );

afterEach( function()
{
    $this->disk->deleteDirectory( $this->path );
} );




it( 'returns an error if the database connection is incorrect', function()
{
    $connection = Config::get( 'database.default' );

    Config::set( "database.connections.{$connection}.database", 'foo' );

    $this->artisan( 'populate' )
        ->expectsOutputToContain( "Database not found. Verify your credentials." )
        ->assertExitCode( 1 );
} );


it( 'returns an error if the database has no migration', function()
{
    $this->artisan( 'populate' )
        ->expectsOutputToContain( "No tables migrated yet, please run artisan migrate." )
        ->assertExitCode( 1 );
} );


it( 'closes gracefully if confirmation has been refused', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/one-database/base' );

    $this->artisan( 'populate', $this->parameters )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'No' )
        ->assertExitCode( 0 );

    $this->artisan( 'migrate:fresh' );
} );


it( 'returns an error if the migration model does not exist', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/one-database/base' );

    $this->artisan( 'populate', $this->parameters )
        ->expectsOutputToContain( "Table 'foo' has changes" )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "App\\Models\\" )
        ->expectsOutputToContain( "The model file was not found." )
        ->assertExitCode( 1 );

    $this->artisan( 'migrate:fresh' );
} );


it( 'updates the empty table columns without converting', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/one-database/base' );

    $this->artisan( 'populate', $this->parameters )
        ->expectsOutputToContain( "Table 'foo' has changes" )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo" )
        ->expectsOutputToContain( "The 'foo' table columns have been updated but it seems the table has no records. Skipping record conversion." )
        ->assertExitCode( 0 );

    $this->artisan( 'migrate:fresh' );
} );


it( 'updates the seeded table columns and recieves an incorrect conversion formula', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/one-database/base' );

    $this->seed( FooSeeder::class );

    $this->artisan( 'populate', $this->parameters )
        ->expectsOutputToContain( "Table 'foo' has changes" )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo" )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", "foo" )
        ->expectsOutputToContain( "The function did not respect the required format." )
        ->assertExitCode( 1 );

    $this->artisan( 'migrate:fresh' );
} );


it( 'updates the seeded table columns and populates successfully', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/one-database/base' );

    $this->seed( FooSeeder::class );

    $this->artisan( 'populate', $this->parameters )
        ->expectsOutputToContain( "Table 'foo' has changes" )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo" )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->expectsQuestion( "How would you like to convert the records for the column 'bar' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->expectsOutputToContain( "Population succeeded." )
        ->assertExitCode( 0 );

    $this->artisan( 'migrate:fresh' );
} );


it( 'updates the seeded table columns and populates successfully on two databases', function()
{
    $this->loadMigrationsFrom( 'tests/app/database/migrations/two-databases/base' );

    $this->seed( [ FooSeeder::class, QuuxSeeder::class ] );

    $parameters = [ '--realpath' => true, '--path' => 'tests/app/database/migrations/two-databases/new', '--database' => [ 'one', 'two' ] ];

    $this->artisan( 'populate', $parameters )
        ->expectsConfirmation( "Do you want to proceed on populating the 'foo' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Foo' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Foo" )
        ->expectsQuestion( "How would you like to convert the records for the column 'qux' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->expectsQuestion( "How would you like to convert the records for the column 'bar' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->expectsConfirmation( "Do you want to proceed on populating the 'quux' table?", 'Yes' )
        ->expectsQuestion( "The 'App\Models\Quux' model path does not exist, please provide the correct path.", "CapsulesCodes\\Population\\Tests\\App\\Models\\New\\Quux" )
        ->expectsQuestion( "How would you like to convert the records for the column 'waldo' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->expectsQuestion( "How would you like to convert the records for the column 'grault' of type 'varchar'?  'fn( \$attribute, \$model ) => \$attribute'", "fn() => fake()->sentence()" )
        ->assertExitCode( 0 );


    $this->artisan( 'db:wipe', [ '--database' => 'two' ] );

    $this->artisan( 'migrate:fresh', [ '--database' => 'two' ] );
} );
